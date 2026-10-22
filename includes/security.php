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
    if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
        return false;
    }

    // PHP's range flags have differed for IPv4-mapped IPv6 addresses. Reduce
    // them to IPv4 explicitly so ::ffff:127.0.0.1 cannot bypass the checks.
    $packed = @inet_pton($ip);
    if ($packed !== false
        && strlen($packed) === 16
        && substr($packed, 0, 12) === str_repeat("\0", 10) . "\xff\xff") {
        $mapped = inet_ntop(substr($packed, 12));
        return is_string($mapped) && isPublicIp($mapped);
    }

    return filter_var(
        $ip,
        FILTER_VALIDATE_IP,
        FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
    ) !== false;
}

/**
 * Parse a remote URL without performing DNS. Only the two existing content
 * servers are proxy targets.
 */
function parseAllowedRemoteUrl(string $url): ?array
{
    if ($url === '' || strlen($url) > 2048 || preg_match('/[\x00-\x20\x7F]/', $url)) {
        return null;
    }

    $parts = parse_url($url);
    if (!is_array($parts)
        || !isset($parts['scheme'], $parts['host'])
        || !in_array(strtolower($parts['scheme']), array('http', 'https'), true)
        || isset($parts['user'])
        || isset($parts['pass'])
        || isset($parts['fragment'])) {
        return null;
    }

    $scheme = strtolower($parts['scheme']);
    $host = strtolower($parts['host']);
    if (!in_array($host, array('download.gisinternals.com', 'build2.gisinternals.com'), true)) {
        return null;
    }

    $port = $parts['port'] ?? ($scheme === 'https' ? 443 : 80);
    if (($scheme === 'https' && $port !== 443) || ($scheme === 'http' && $port !== 80)) {
        return null;
    }

    return array(
        'url' => $url,
        'scheme' => $scheme,
        'host' => $host,
        'port' => $port,
    );
}

/**
 * Resolve once and fail closed if any returned address is not globally
 * routable. The optional resolver is a test seam; production uses DNS.
 */
function resolvePublicIps(string $host, ?callable $resolver = null): ?array
{
    $records = $resolver === null
        ? dns_get_record($host, DNS_A | DNS_AAAA)
        : $resolver($host);
    if ($records === false || $records === array()) {
        return null;
    }

    $addresses = array();
    foreach ($records as $record) {
        if (!is_array($record)) {
            return null;
        }
        $ip = $record['ip'] ?? $record['ipv6'] ?? null;
        if (!is_string($ip) || !isPublicIp($ip)) {
            return null;
        }
        $addresses[$ip] = true;
    }

    return $addresses === array() ? null : array_keys($addresses);
}

function isAllowedRemoteUrl(string $url, ?callable $resolver = null): bool
{
    $target = parseAllowedRemoteUrl($url);
    return $target !== null && resolvePublicIps($target['host'], $resolver) !== null;
}

function curlResolveAddress(string $ip): string
{
    return strpos($ip, ':') === false ? $ip : '[' . $ip . ']';
}

/**
 * Build cURL options separately so the security properties are regression
 * testable without contacting a live external service.
 */
function buildPinnedCurlOptions(array $target, string $ip): array
{
    return array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_MAXREDIRS => 0,
        CURLOPT_CONNECTTIMEOUT => 7,
        CURLOPT_TIMEOUT => 7,
        CURLOPT_USERAGENT => 'GISInternals content proxy',
        CURLOPT_HTTPGET => true,
        CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
        CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_PROXY => '',
        CURLOPT_NOPROXY => '*',
        CURLOPT_RESOLVE => array(
            $target['host'] . ':' . $target['port'] . ':' . curlResolveAddress($ip),
        ),
    );
}

/**
 * Connect to one already-validated IP while retaining the URL hostname for
 * HTTP Host, TLS SNI, and certificate hostname verification.
 */
function fetchPinnedRemote(array $target, string $ip)
{
    if (!extension_loaded('curl')) {
        return false;
    }

    $handle = curl_init($target['url']);
    if ($handle === false) {
        return false;
    }
    if (!curl_setopt_array($handle, buildPinnedCurlOptions($target, $ip))) {
        curl_close($handle);
        return false;
    }

    $result = curl_exec($handle);
    $status = curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
    curl_close($handle);

    if (!is_string($result) || $status < 200 || $status >= 300) {
        return false;
    }

    return $result;
}

/**
 * Resolve and validate exactly once, then pin cURL to one of those addresses.
 * The optional callbacks are used only by deterministic regression tests.
 */
function fetchAllowedRemote(string $url, ?callable $resolver = null, ?callable $fetcher = null)
{
    $target = parseAllowedRemoteUrl($url);
    if ($target === null) {
        return false;
    }

    $addresses = resolvePublicIps($target['host'], $resolver);
    if ($addresses === null) {
        return false;
    }

    $fetcher = $fetcher ?? 'fetchPinnedRemote';
    foreach ($addresses as $ip) {
        $result = $fetcher($target, $ip);
        if (is_string($result)) {
            return $result;
        }
    }

    return false;
}

function safePublicHost(array $server): string
{
    $host = strtolower(requestString($server, 'HTTP_HOST') ?? '');
    if (in_array($host, array(
        'www.gisinternals.com',
        'download.gisinternals.com',
        'build2.gisinternals.com',
    ), true)) {
        return $host;
    }

    return 'www.gisinternals.com';
}
