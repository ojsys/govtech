<?php
declare(strict_types=1);

namespace App\Models;

/**
 * Editable site content. A thin layer over site_settings (via Setting) that adds
 * a declarative schema with default values — the single source of truth for both
 * the admin editor and the frontend. Frontend reads Content::get('hero_title_em');
 * the admin edits the same keys grouped by section.
 *
 * Field types: text | textarea | number | image.
 */
final class Content
{
    /** value override (site_settings) ?? schema default ?? ''. */
    public static function get(string $key, ?string $fallback = null): string
    {
        $val = Setting::get($key);
        if ($val !== null && $val !== '') {
            return $val;
        }
        return $fallback ?? self::defaults()[$key] ?? '';
    }

    /** Public URL for an uploaded branding image (logo/favicon), or '' if unset. */
    public static function image(string $key): string
    {
        $file = Setting::get($key);
        if (!$file) {
            return '';
        }
        if (preg_match('#^https?://#', $file)) {
            return $file;
        }
        return rtrim((string) \Config::get('app.uploads_url', '/uploads'), '/') . '/' . ltrim($file, '/');
    }

    public static function set(string $key, string $value): void
    {
        Setting::set($key, $value);
    }

    /** Flattened key => default map, derived from the schema. */
    public static function defaults(): array
    {
        $out = [];
        foreach (self::schema() as $section) {
            foreach ($section['fields'] as $f) {
                $out[$f['key']] = $f['default'] ?? '';
            }
        }
        return $out;
    }

    public static function section(string $slug): ?array
    {
        return self::schema()[$slug] ?? null;
    }

    /**
     * The editable content map: section slug => [title, icon-less, fields[]].
     */
    public static function schema(): array
    {
        return [
            'branding' => [
                'title' => 'Branding',
                'intro' => 'Logo, favicon and the name shown in the header and footer.',
                'fields' => [
                    ['key' => 'logo', 'label' => 'Logo image (optional — replaces the seal mark)', 'type' => 'image', 'default' => ''],
                    ['key' => 'favicon', 'label' => 'Favicon (PNG recommended, square)', 'type' => 'image', 'default' => ''],
                    ['key' => 'brand_name', 'label' => 'Brand name', 'type' => 'text', 'default' => 'GovTech'],
                    ['key' => 'brand_sub', 'label' => 'Brand subtitle', 'type' => 'text', 'default' => 'Conference & Awards · NG'],
                ],
            ],
            'hero' => [
                'title' => 'Hero section',
                'intro' => 'The top of the home page. Dates and venue come from Event details.',
                'fields' => [
                    ['key' => 'hero_eyebrow', 'label' => 'Eyebrow', 'type' => 'text', 'default' => 'Bureau of Public Service Reforms · The Presidency'],
                    ['key' => 'hero_title_lead', 'label' => 'Headline — line 1', 'type' => 'text', 'default' => 'Redefining Possibilities:'],
                    ['key' => 'hero_title_em', 'label' => 'Headline — highlighted words', 'type' => 'text', 'default' => 'Emerging Technology'],
                    ['key' => 'hero_title_tail', 'label' => 'Headline — after highlight', 'type' => 'text', 'default' => 'for Public Service.'],
                    ['key' => 'hero_lede', 'label' => 'Sub-text', 'type' => 'textarea', 'default' => "The premier gathering where government officials, innovators, policymakers and industry shape Nigeria's digital transformation — and honour those leading it."],
                    ['key' => 'hero_format', 'label' => 'Format label', 'type' => 'text', 'default' => 'In-person & Livestream'],
                    ['key' => 'hero_cta_primary', 'label' => 'Primary button text', 'type' => 'text', 'default' => 'Register to Attend'],
                    ['key' => 'hero_cta_secondary', 'label' => 'Secondary button text', 'type' => 'text', 'default' => 'Submit a Nomination'],
                ],
            ],
            'stats' => [
                'title' => 'Stats band',
                'intro' => 'The four figures under the hero.',
                'fields' => [
                    ['key' => 'stat1_num', 'label' => 'Stat 1 — number', 'type' => 'text', 'default' => '1500'],
                    ['key' => 'stat1_suffix', 'label' => 'Stat 1 — suffix', 'type' => 'text', 'default' => '+'],
                    ['key' => 'stat1_label', 'label' => 'Stat 1 — label', 'type' => 'text', 'default' => 'Delegates expected'],
                    ['key' => 'stat2_num', 'label' => 'Stat 2 — number', 'type' => 'text', 'default' => '60'],
                    ['key' => 'stat2_suffix', 'label' => 'Stat 2 — suffix', 'type' => 'text', 'default' => '+'],
                    ['key' => 'stat2_label', 'label' => 'Stat 2 — label', 'type' => 'text', 'default' => 'Speakers & panelists'],
                    ['key' => 'stat3_num', 'label' => 'Stat 3 — number', 'type' => 'text', 'default' => '120'],
                    ['key' => 'stat3_suffix', 'label' => 'Stat 3 — suffix', 'type' => 'text', 'default' => '+'],
                    ['key' => 'stat3_label', 'label' => 'Stat 3 — label', 'type' => 'text', 'default' => 'Government MDAs'],
                    ['key' => 'stat4_num', 'label' => 'Stat 4 — number', 'type' => 'text', 'default' => '3'],
                    ['key' => 'stat4_suffix', 'label' => 'Stat 4 — suffix', 'type' => 'text', 'default' => 'rd'],
                    ['key' => 'stat4_label', 'label' => 'Stat 4 — label', 'type' => 'text', 'default' => 'Annual edition'],
                ],
            ],
            'about' => [
                'title' => 'About section',
                'intro' => 'The about block and the three objective cards (home + About page).',
                'fields' => [
                    ['key' => 'about_eyebrow', 'label' => 'Eyebrow', 'type' => 'text', 'default' => 'About the Conference'],
                    ['key' => 'about_heading', 'label' => 'Heading', 'type' => 'text', 'default' => 'A national platform for digital transformation in government.'],
                    ['key' => 'about_p1', 'label' => 'Paragraph 1', 'type' => 'textarea', 'default' => 'The Nigeria GovTech Conference & Awards convenes government officials, technology innovators, industry experts, policymakers and key stakeholders to advance digital transformation across the public sector — through keynotes, panel discussions, hands-on workshops and an awards ceremony celebrating outstanding contributions to the GovTech landscape.'],
                    ['key' => 'about_p2', 'label' => 'Paragraph 2', 'type' => 'textarea', 'default' => "It is where policy meets practice, and where the people building Nigeria's digital future come together."],
                    ['key' => 'about_image', 'label' => 'About image (the photo beside the text)', 'type' => 'image', 'default' => ''],
                    ['key' => 'obj1_title', 'label' => 'Objective 1 — title', 'type' => 'text', 'default' => 'Promote Digital Transformation'],
                    ['key' => 'obj1_desc', 'label' => 'Objective 1 — text', 'type' => 'textarea', 'default' => 'Drive the integration of technology across government processes to enhance efficiency and service delivery.'],
                    ['key' => 'obj2_title', 'label' => 'Objective 2 — title', 'type' => 'text', 'default' => 'Showcase Innovation'],
                    ['key' => 'obj2_desc', 'label' => 'Objective 2 — text', 'type' => 'textarea', 'default' => 'Provide a stage for presenting cutting-edge GovTech solutions from across the continent and beyond.'],
                    ['key' => 'obj3_title', 'label' => 'Objective 3 — title', 'type' => 'text', 'default' => 'Build Capacity'],
                    ['key' => 'obj3_desc', 'label' => 'Objective 3 — text', 'type' => 'textarea', 'default' => 'Equip public servants with practical skills through training sessions and workshops led by experts.'],
                ],
            ],
            'awards' => [
                'title' => 'Awards section',
                'intro' => 'The awards block on the home page.',
                'fields' => [
                    ['key' => 'awards_eyebrow', 'label' => 'Eyebrow', 'type' => 'text', 'default' => 'The Nigeria GovTech Awards'],
                    ['key' => 'awards_heading_lead', 'label' => 'Heading — start', 'type' => 'text', 'default' => 'Honouring the'],
                    ['key' => 'awards_heading_em', 'label' => 'Heading — highlighted', 'type' => 'text', 'default' => 'trailblazers'],
                    ['key' => 'awards_heading_tail', 'label' => 'Heading — end', 'type' => 'text', 'default' => 'of public-sector technology.'],
                    ['key' => 'awards_body', 'label' => 'Body text', 'type' => 'textarea', 'default' => 'The Awards recognise individuals and organisations that have made significant contributions to GovTech in Nigeria — the IT pioneers within leading government entities and private organisations who have demonstrated excellence and outstanding leadership over the past twelve months.'],
                    ['key' => 'awards_est', 'label' => 'Medallion label', 'type' => 'text', 'default' => 'EST. 2024'],
                ],
            ],
            'footer' => [
                'title' => 'Footer & contact',
                'intro' => 'Footer tagline, contact email, organiser and social links.',
                'fields' => [
                    ['key' => 'footer_tagline', 'label' => 'Footer tagline', 'type' => 'textarea', 'default' => "The premier gathering for digital transformation in Nigeria's public service, organized by the Bureau of Public Service Reforms, The Presidency."],
                    ['key' => 'contact_email', 'label' => 'Contact email', 'type' => 'text', 'default' => 'info@govtechconference.ng'],
                    ['key' => 'partnerships_email', 'label' => 'Partnerships email', 'type' => 'text', 'default' => 'partnerships@govtechconference.ng'],
                    ['key' => 'organizer_name', 'label' => 'Organiser name', 'type' => 'text', 'default' => 'Bureau of Public Service Reforms'],
                    ['key' => 'organizer_note', 'label' => 'Organiser note', 'type' => 'text', 'default' => 'Organized by the Presidency, Federal Republic of Nigeria'],
                    ['key' => 'social_linkedin', 'label' => 'LinkedIn URL', 'type' => 'text', 'default' => '#'],
                    ['key' => 'social_twitter', 'label' => 'X / Twitter URL', 'type' => 'text', 'default' => '#'],
                    ['key' => 'countdown_target', 'label' => 'Countdown target (ISO 8601)', 'type' => 'text', 'default' => '2026-10-07T09:00:00+01:00'],
                ],
            ],
        ];
    }
}
