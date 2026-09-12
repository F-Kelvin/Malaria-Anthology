<?php

declare(strict_types=1);

/**
 * Escape output safely for HTML.
 */
function e(?string $value): string
{
    return htmlspecialchars(
        $value ?? '',
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

/**
 * Redirect the browser to another URL.
 */
function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

/**
 * Generate a URL-friendly slug.
 */
function create_slug(string $text): string
{
    $text = trim($text);

    $text = preg_replace('/[^\p{L}\p{N}]+/u', '-', $text);
    $text = trim($text ?? '', '-');

    return strtolower($text);
}

/**
 * Return the application's base URL.
 */
function base_url(string $path = ''): string
{
    $base = '/malaria-anthology';

    return $base . ($path !== '' ? '/' . ltrim($path, '/') : '');
}