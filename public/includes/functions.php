<?php

function start_session(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function get_csrf_token(): string
{
    start_session();

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function validate_csrf_token(?string $token): bool
{
    start_session();

    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string) $token);
}

function generate_token(int $length = 32): string
{
    return rtrim(strtr(base64_encode(random_bytes($length)), '+/', '-_'), '=');
}

function sanitize_filename(string $filename): string
{
    $filename = preg_replace('/[^A-Za-z0-9_\-. ]/', '', $filename) ?? '';
    $filename = trim($filename);

    return $filename ?: 'file';
}

function format_bytes(int $bytes, int $precision = 2): string
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
    $pow = min($pow, count($units) - 1);
    $bytes /= 1024 ** $pow;

    return round($bytes, $precision) . ' ' . $units[$pow];
}

function remaining_time_string(DateTimeImmutable $expiry): string
{
    $now = new DateTimeImmutable('now');

    if ($expiry <= $now) {
        return 'Expired';
    }

    $interval = $now->diff($expiry);

    $parts = [];
    if ($interval->d > 0) {
        $parts[] = $interval->d . 'd';
    }
    if ($interval->h > 0) {
        $parts[] = $interval->h . 'h';
    }
    if ($interval->i > 0) {
        $parts[] = $interval->i . 'm';
    }
    if ($interval->d === 0 && $interval->h === 0 && $interval->i === 0) {
        $parts[] = $interval->s . 's';
    }

    return implode(' ', $parts);
}

function is_forbidden_mime(string $mime): bool
{
    $forbidden = [
        'application/x-httpd-php',
        'text/x-php',
        'text/html',
        'application/x-msdownload',
        'application/x-msdos-program',
        'application/x-executable',
        'application/x-sh',
        'application/x-csh',
    ];

    if (in_array($mime, $forbidden, true)) {
        return true;
    }

    return str_starts_with($mime, 'application/x-sh');
}

function format_datetime(DateTimeImmutable $dateTime): string
{
    return $dateTime->format('M d, Y \a\t H:i');
}
