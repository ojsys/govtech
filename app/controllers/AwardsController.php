<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Mailer;
use App\Core\RateLimit;
use App\Core\Request;
use App\Core\Validator;
use App\Core\View;
use App\Models\AwardCategory;
use App\Models\Nomination;
use App\Models\Vote;

final class AwardsController extends Controller
{
    /** GET /awards — categories, shortlisted nominees, voting UI. */
    public function index(Request $request, array $args = []): void
    {
        $categories = AwardCategory::active();
        $nominees = [];
        foreach ($categories as $c) {
            $nominees[(int) $c['id']] = Nomination::votableInCategory((int) $c['id']);
        }
        $this->render('pages/awards', [
            'pageTitle'  => 'Awards',
            'categories' => $categories,
            'nominees'   => $nominees,
            'voting'     => true,
        ]);
    }

    /** GET /awards/nominate — nomination form. */
    public function nominateForm(Request $request, array $args = []): void
    {
        $this->render('pages/nominate', [
            'pageTitle'  => 'Submit a nomination',
            'categories' => AwardCategory::active(),
            'errors'     => [],
        ]);
    }

    /** POST /awards/nominate — create a pending nomination. */
    public function nominate(Request $request, array $args = []): void
    {
        Csrf::verify($request);
        if (!RateLimit::allow('nominate:' . $request->ip(), 5, 120) || !RateLimit::honeypotPassed($request)) {
            flash('error', 'Too many submissions. Please wait a moment and try again.');
            redirect('/awards/nominate');
        }

        $data = [
            'category_id'     => $request->int('category_id'),
            'nominee_name'    => $request->str('nominee_name'),
            'nominee_org'     => $request->str('nominee_org'),
            'nominee_email'   => $request->str('nominee_email'),
            'nominator_name'  => $request->str('nominator_name'),
            'nominator_email' => $request->str('nominator_email'),
            'justification'   => $request->str('justification'),
        ];
        $v = new Validator($data, [
            'category_id'     => 'required|int',
            'nominee_name'    => 'required|max:160',
            'nominator_name'  => 'required|max:160',
            'nominator_email' => 'required|email|max:160',
            'justification'   => 'required|min:20|max:2000',
            'nominee_email'   => 'email',
        ], ['justification' => 'Reason for nomination']);

        // Category must be valid + active.
        if (!$v->fails() && !AwardCategory::activeById((int) $data['category_id'])) {
            $errors = ['category_id' => 'Please choose a valid award category.'];
        }
        if ($v->fails() || isset($errors)) {
            $_SESSION['_old'] = $data;
            $this->render('pages/nominate', [
                'pageTitle'  => 'Submit a nomination',
                'categories' => AwardCategory::active(),
                'errors'     => $errors ?? $v->errors(),
            ]);
            return;
        }

        Nomination::create($data);
        flash('ok', 'Thank you — your nomination has been submitted for review.');
        redirect('/awards/nominate');
    }

    /** POST /awards/vote — cast a vote (sends an email verification link). */
    public function vote(Request $request, array $args = []): void
    {
        Csrf::verify($request);
        if (!RateLimit::allow('vote:' . $request->ip(), 10, 120) || !RateLimit::honeypotPassed($request)) {
            $this->voteRedirect('error', 'Too many attempts. Please wait a moment and try again.');
        }

        $nominationId = $request->int('nomination_id');
        $email = $request->str('email');

        $v = new Validator(['email' => $email], ['email' => 'required|email|max:160']);
        if ($v->fails()) {
            $this->voteRedirect('error', 'Please enter a valid email address to vote.');
        }

        $nomination = Nomination::findVotable($nominationId);
        if (!$nomination) {
            $this->voteRedirect('error', 'That nominee is not open for voting.');
        }

        $res = Vote::cast($nominationId, (int) $nomination['category_id'], $email, $request->ip());

        if ($res['status'] === 'already_voted') {
            $this->voteRedirect('error', 'You have already voted in this category. Only one vote per category is allowed.');
        }

        // sent or resent — email the verification link.
        $this->sendVerifyEmail($email, $nomination, $res['token']);
        $msg = $res['status'] === 'resent'
            ? 'We re-sent your verification link to ' . $email . '. Your vote counts once you confirm it.'
            : 'Almost there! Check ' . $email . ' for a link to confirm your vote.';
        $this->voteRedirect('ok', $msg);
    }

    /** GET /awards/vote/verify?token=... — confirm a vote (idempotent). */
    public function verify(Request $request, array $args = []): void
    {
        $res = Vote::verify($request->str('token'));
        $map = [
            'verified' => ['Vote confirmed', 'Thank you — your vote has been counted. You can follow the live tally below.'],
            'already'  => ['Already confirmed', 'This vote was already confirmed. Each vote counts once.'],
            'invalid'  => ['Link not valid', 'This verification link is invalid or has expired. Please vote again.'],
        ];
        [$title, $body] = $map[$res['result']] ?? $map['invalid'];
        $this->render('pages/vote-result', [
            'pageTitle' => $title,
            'title'     => $title,
            'body'      => $body,
            'ok'        => $res['result'] === 'verified' || $res['result'] === 'already',
        ]);
    }

    /** GET /awards/results — live tally of verified votes. */
    public function results(Request $request, array $args = []): void
    {
        $categories = AwardCategory::active();
        $nominees = [];
        $totals = [];
        foreach ($categories as $c) {
            $list = Nomination::votableInCategory((int) $c['id']);
            $nominees[(int) $c['id']] = $list;
            $totals[(int) $c['id']] = array_sum(array_map(static fn($n) => (int) $n['votes_count'], $list));
        }
        $this->render('pages/awards-results', [
            'pageTitle'  => 'Live results',
            'categories' => $categories,
            'nominees'   => $nominees,
            'totals'     => $totals,
        ]);
    }

    private function voteRedirect(string $type, string $message): never
    {
        flash($type, $message);
        redirect('/awards');
    }

    private function sendVerifyEmail(string $email, array $nomination, string $token): void
    {
        $view = new View();
        $html = $view->renderPartial('emails/vote-verify', [
            'nominee' => $nomination,
            'link'    => url('/awards/vote/verify?token=' . $token),
        ]);
        (new Mailer())->send($email, '', 'Confirm your vote — Nigeria GovTech Awards', $html);
    }
}
