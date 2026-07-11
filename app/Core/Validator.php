<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Tiny rule-based validator. Rules: required, email, min:n, max:n, int, in:a,b,c.
 * Usage:
 *   $v = new Validator($data, ['email' => 'required|email', 'qty' => 'required|int']);
 *   if ($v->fails()) { ... $v->errors() ... }
 */
final class Validator
{
    private array $errors = [];

    public function __construct(private array $data, private array $rules, private array $labels = [])
    {
        $this->run();
    }

    private function run(): void
    {
        foreach ($this->rules as $field => $ruleStr) {
            $value = $this->data[$field] ?? null;
            $value = is_string($value) ? trim($value) : $value;
            foreach (explode('|', $ruleStr) as $rule) {
                [$name, $arg] = array_pad(explode(':', $rule, 2), 2, null);
                if (!$this->check($name, $value, $arg)) {
                    $this->errors[$field] ??= $this->message($field, $name, $arg);
                    break; // one error per field
                }
            }
        }
    }

    private function check(string $rule, mixed $value, ?string $arg): bool
    {
        return match ($rule) {
            'required' => $value !== null && $value !== '',
            'email'    => $value === null || $value === '' || filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
            'int'      => $value === null || $value === '' || preg_match('/^-?\d+$/', (string) $value) === 1,
            'min'      => $value === null || mb_strlen((string) $value) >= (int) $arg,
            'max'      => $value === null || mb_strlen((string) $value) <= (int) $arg,
            'in'       => $value === null || $value === '' || in_array((string) $value, explode(',', (string) $arg), true),
            default    => true,
        };
    }

    private function message(string $field, string $rule, ?string $arg): string
    {
        $label = $this->labels[$field] ?? ucfirst(str_replace('_', ' ', $field));
        return match ($rule) {
            'required' => "{$label} is required.",
            'email'    => "{$label} must be a valid email address.",
            'int'      => "{$label} must be a whole number.",
            'min'      => "{$label} must be at least {$arg} characters.",
            'max'      => "{$label} must be no more than {$arg} characters.",
            'in'       => "{$label} is invalid.",
            default    => "{$label} is invalid.",
        };
    }

    public function fails(): bool { return $this->errors !== []; }
    public function passes(): bool { return $this->errors === []; }
    public function errors(): array { return $this->errors; }
    public function first(): ?string { return $this->errors[array_key_first($this->errors)] ?? null; }
}
