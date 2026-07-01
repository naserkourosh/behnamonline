<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Tiny rule-based validator. Returns errors keyed by field.
 * Rules: required, int, min:N, max:N, in:a,b,c, mobile (Iranian).
 */
final class Validator
{
    /** @var array<string,string> */
    private array $errors = [];

    /**
     * @param array<string,mixed> $data
     * @param array<string,string> $rules  field => 'rule1|rule2:arg'
     */
    public function validate(array $data, array $rules): bool
    {
        foreach ($rules as $field => $ruleSet) {
            $value = $data[$field] ?? null;
            foreach (explode('|', $ruleSet) as $rule) {
                [$name, $arg] = array_pad(explode(':', $rule, 2), 2, null);
                $this->applyRule($field, $value, $name, $arg);
                if (isset($this->errors[$field])) {
                    break;
                }
            }
        }
        return $this->errors === [];
    }

    private function applyRule(string $field, mixed $value, string $name, ?string $arg): void
    {
        switch ($name) {
            case 'required':
                if ($value === null || $value === '' || (is_array($value) && $value === [])) {
                    $this->errors[$field] = 'این فیلد الزامی است.';
                }
                break;
            case 'int':
                if ($value !== null && $value !== '' && filter_var($value, FILTER_VALIDATE_INT) === false) {
                    $this->errors[$field] = 'مقدار باید عددی باشد.';
                }
                break;
            case 'min':
                if (is_numeric($value) && (int) $value < (int) $arg) {
                    $this->errors[$field] = "حداقل مقدار {$arg} است.";
                }
                break;
            case 'max':
                if (is_numeric($value) && (int) $value > (int) $arg) {
                    $this->errors[$field] = "حداکثر مقدار {$arg} است.";
                }
                break;
            case 'in':
                $allowed = explode(',', (string) $arg);
                if ($value !== null && !in_array((string) $value, $allowed, true)) {
                    $this->errors[$field] = 'مقدار نامعتبر است.';
                }
                break;
            case 'mobile':
                if ($value !== null && $value !== '' && !preg_match('/^09\d{9}$/', (string) $value)) {
                    $this->errors[$field] = 'شماره موبایل معتبر نیست.';
                }
                break;
        }
    }

    /** @return array<string,string> */
    public function errors(): array
    {
        return $this->errors;
    }
}
