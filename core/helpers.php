<?php
declare(strict_types=1);

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        $cleanPath = trim($path);
        if ($cleanPath === '') {
            return BASE_URL === '' ? '/' : BASE_URL . '/';
        }

        return (BASE_URL === '' ? '' : BASE_URL) . '/' . ltrim($cleanPath, '/');
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return url('public/' . ltrim($path, '/'));
    }
}

if (!function_exists('isActiveRoute')) {
    function isActiveRoute(string $path): bool
    {
        $uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $normalizedUri = $normalizedUri === '/' ? '/' : rtrim($normalizedUri, '/');

        $basePath = BASE_URL === '' ? '' : BASE_URL;
        if ($basePath !== '' && str_starts_with($normalizedUri, $basePath)) {
            $normalizedUri = substr($normalizedUri, strlen($basePath));
            $normalizedUri = $normalizedUri === '' ? '/' : $normalizedUri;
        }

        $target = '/' . trim($path, '/');
        $target = $target === '/' ? '/' : rtrim($target, '/');

        return $normalizedUri === $target;
    }
}
