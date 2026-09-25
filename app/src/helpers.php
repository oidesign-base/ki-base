<?php
/**
 * Global helper functions used in controllers and views.
 */

declare(strict_types=1);

use App\Core\Config;
use App\Core\Csrf;
use App\Core\Lang;
use App\Core\Session;

/** Read a configuration value using dot notation, e.g. config('db.name'). */
function config(string $key, mixed $default = null): mixed
{
    return Config::get($key, $default);
}

/** Escape a value for safe HTML output. */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Translate a UI string (app/lang/pl.php). Placeholders: {name}. */
function t(string $key, array $params = []): string
{
    return Lang::get($key, $params);
}

/** Absolute path inside the panel, e.g. url('/batches'). */
function url(string $path = '/'): string
{
    return '/' . ltrim($path, '/');
}

/** URL of a static asset with a cache-busting version parameter. */
function asset(string $path): string
{
    $path = ltrim($path, '/');
    $file = dirname(APP_ROOT) . '/public/' . $path;
    $version = is_file($file) ? (string) filemtime($file) : '';
    return '/' . $path . ($version !== '' ? '?v=' . $version : '');
}

/** Hidden input with the CSRF token for POST forms. */
function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(Csrf::token()) . '">';
}

/** Previously submitted form value (after a validation error redirect). */
function old(string $key, string $default = ''): string
{
    $old = Session::get('_old_input', []);
    return is_array($old) && isset($old[$key]) && is_scalar($old[$key]) ? (string) $old[$key] : $default;
}

/** Format a UTC database timestamp for display in the panel timezone. */
function format_datetime(?string $utc, string $format = 'd.m.Y H:i'): string
{
    if ($utc === null || $utc === '') {
        return '';
    }
    $dt = new DateTimeImmutable($utc, new DateTimeZone('UTC'));
    return $dt->setTimezone(new DateTimeZone((string) config('app.timezone')))->format($format);
}

/** Format an accounting DATE value (already local, no conversion). */
function format_date(?string $date, string $format = 'd.m.Y'): string
{
    if ($date === null || $date === '') {
        return '';
    }
    return (new DateTimeImmutable($date))->format($format);
}

/** Format a money amount the Polish way: 1 234,50. */
function format_money(float|string|null $amount, string $currency = 'PLN'): string
{
    if ($amount === null || $amount === '') {
        return '';
    }
    return number_format((float) $amount, 2, ',', "\u{00A0}") . "\u{00A0}" . $currency;
}

/** Validation error for a form field ('' when none). */
function field_error(string $field): string
{
    $errors = Session::get('_errors', []);
    return is_array($errors) && isset($errors[$field]) ? (string) $errors[$field] : '';
}

/** ' is-invalid' when the field has a validation error (Bootstrap class). */
function invalid_class(string $field): string
{
    return field_error($field) !== '' ? ' is-invalid' : '';
}

/** Invalid-feedback block for a field (empty string when no error). */
function field_feedback(string $field): string
{
    $error = field_error($field);
    return $error === '' ? '' : '<div class="invalid-feedback d-block">' . e($error) . '</div>';
}

/** One or two letters for a user avatar: "Aleksy" -> "A", "Kate Ivanova" -> "KI". */
function user_initials(string $name): string
{
    $parts = preg_split('/\s+/u', trim($name), -1, PREG_SPLIT_NO_EMPTY) ?: ['?'];
    $initials = mb_strtoupper(mb_substr($parts[0], 0, 1));
    if (count($parts) > 1) {
        $initials .= mb_strtoupper(mb_substr($parts[count($parts) - 1], 0, 1));
    }
    return $initials;
}
