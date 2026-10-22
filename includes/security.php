<?php

/**
 * Shared input and path validation for the public PHP endpoints.
 *
 * Keep this file compatible with the oldest supported runtime (PHP 8.1).
 */

function requestString(array $source, string $name): ?string
{
    if (!isset($source[$name]) || !is_string($source[$name])) {
        return null;
    }

    return $source[$name];
}

function escapeHtml(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function isSafeLeafName(string $name): bool
{
    return $name !== ''
        && $name !== '.'
        && $name !== '..'
        && strpos($name, "\0") === false
        && strpos($name, '/') === false
        && strpos($name, '\\') === false
        && !preg_match('/[\x00-\x1F\x7F]/', $name);
}

/**
 * Resolve an existing regular file below a trusted directory.
 */
function resolveFileBelow(string $root, string $relativePath): ?string
{
    if ($relativePath === '' || strpos($relativePath, "\0") !== false) {
        return null;
    }

    // Treat both separator styles as separators on every operating system.
    $relativePath = str_replace('\\', '/', $relativePath);
    if ($relativePath[0] === '/' || preg_match('/^[A-Za-z]:/', $relativePath)) {
        return null;
    }

    foreach (explode('/', $relativePath) as $segment) {
        if ($segment === '' || $segment === '.' || $segment === '..') {
            return null;
        }
    }

    $rootPath = realpath($root);
    $filePath = realpath($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath));
    if ($rootPath === false || $filePath === false || !is_file($filePath)) {
        return null;
    }

    $prefix = rtrim($rootPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    if (strncmp($filePath, $prefix, strlen($prefix)) !== 0) {
        return null;
    }

    return $filePath;
}

function isValidJsonpCallback(string $callback): bool
{
    return strlen($callback) <= 128
        && preg_match('/^[A-Za-z_$][A-Za-z0-9_$]*(?:\.[A-Za-z_$][A-Za-z0-9_$]*)*$/D', $callback) === 1;
}

function isPublicIp(string $ip): bool
{
    return filter_var(
        $ip,
        FILTER_VALIDATE_IP,
        FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
    ) !== false;
}

/**
 * Only GISInternals web servers are valid proxy targets. DNS results are also
 * checked so an accidentally private record cannot turn this into an SSRF
 * path to the local network.
 */
function isAllowedRemoteUrl(string $url): bool
{
    if ($url === '' || strlen($url) > 2048 || preg_match('/[\x00-\x20\x7F]/', $url)) {
        return false;
    }

    $parts = parse_url($url);
    if (!is_array($parts)
        || !isset($parts['scheme'], $parts['host'])
        || !in_array(strtolower($parts['scheme']), array('http', 'https'), true)
        || isset($parts['user'])
        || isset($parts['pass'])
        || isset($parts['fragment'])) {
        return false;
    }

    $host = strtolower(rtrim($parts['host'], '.'));
    if ($host !== 'gisinternals.com' && !str_ends_with($host, '.gisinternals.com')) {
        return false;
    }

    if (isset($parts['port']) && !in_array($parts['port'], array(80, 443), true)) {
        return false;
    }

    $records = dns_get_record($host, DNS_A | DNS_AAAA);
    if ($records === false || $records === array()) {
        return false;
    }

    foreach ($records as $record) {
        $ip = $record['ip'] ?? $record['ipv6'] ?? null;
        if (!is_string($ip) || !isPublicIp($ip)) {
            return false;
        }
    }

    return true;
}

function fetchAllowedRemote(string $url)
{
    if (!isAllowedRemoteUrl($url)) {
        return false;
    }

    $context = stream_context_create(array(
        'http' => array(
            'follow_location' => 0,
            'ignore_errors' => false,
            'timeout' => 7,
            'user_agent' => 'GISInternals content proxy',
        ),
        'ssl' => array(
            'verify_peer' => true,
            'verify_peer_name' => true,
        ),
    ));

    return @file_get_contents($url, false, $context);
}

function safePublicHost(array $server): string
{
    $host = requestString($server, 'HTTP_HOST') ?? '';
    if (preg_match('/^(?:[A-Za-z0-9-]+\.)*gisinternals\.com(?::(?:80|443))?$/D', $host) === 1) {
        return $host;
    }

    return 'www.gisinternals.com';
}
