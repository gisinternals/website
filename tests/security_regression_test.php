<?php

require_once __DIR__ . '/../includes/security.php';

$failures = array();

function check(bool $condition, string $message): void
{
    global $failures;
    if (!$condition) {
        $failures[] = $message;
    }
}

function runEndpoint(string $endpoint, array $get, array $server = array()): array
{
    $root = realpath(__DIR__ . '/..');
    $bootstrap = '$_GET=json_decode(base64_decode(' . var_export(base64_encode(json_encode($get)), true) . '),true);'
        . '$_SERVER=json_decode(base64_decode(' . var_export(base64_encode(json_encode($server)), true) . '),true);'
        . 'chdir(' . var_export($root, true) . ');'
        . 'include ' . var_export($root . DIRECTORY_SEPARATOR . $endpoint, true) . ';';
    $command = escapeshellarg(PHP_BINARY) . ' -d display_errors=1 -r ' . escapeshellarg($bootstrap);
    $output = array();
    $status = 0;
    exec($command, $output, $status);
    return array(implode("\n", $output), $status);
}

check(isValidJsonpCallback('jQuery1720123_456'), 'jQuery callback should be accepted');
check(isValidJsonpCallback('client.callbacks.done'), 'dotted callback should be accepted');
check(!isValidJsonpCallback('alert(1)//'), 'executable callback should be rejected');
check(!isValidJsonpCallback('x;window.pwned()'), 'statement callback should be rejected');

check(!isAllowedRemoteUrl('file:///etc/passwd'), 'file URL should be rejected');
check(!isAllowedRemoteUrl('http://127.0.0.1/admin'), 'loopback URL should be rejected');
check(!isAllowedRemoteUrl('http://169.254.169.254/latest/meta-data'), 'metadata URL should be rejected');
check(!isAllowedRemoteUrl('https://gisinternals.com.evil.example/'), 'lookalike domain should be rejected');
check(!isAllowedRemoteUrl('https://user@gisinternals.com/'), 'credential-bearing URL should be rejected');
check(safePublicHost(array('HTTP_HOST' => 'download.gisinternals.com')) === 'download.gisinternals.com', 'known public host should be retained');
check(safePublicHost(array('HTTP_HOST' => 'evil.example\"><script>alert(1)</script>')) === 'www.gisinternals.com', 'untrusted Host header should use the safe default');

$temporaryRoot = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'gisinternals-security-' . bin2hex(random_bytes(6));
mkdir($temporaryRoot);
mkdir($temporaryRoot . DIRECTORY_SEPARATOR . 'downloads');
file_put_contents($temporaryRoot . DIRECTORY_SEPARATOR . 'downloads' . DIRECTORY_SEPARATOR . 'allowed.zip', 'download');
file_put_contents($temporaryRoot . DIRECTORY_SEPARATOR . 'secret.txt', 'secret');

check(resolveFileBelow($temporaryRoot . DIRECTORY_SEPARATOR . 'downloads', 'allowed.zip') !== null, 'file below root should resolve');
check(resolveFileBelow($temporaryRoot . DIRECTORY_SEPARATOR . 'downloads', '../secret.txt') === null, 'parent traversal should be rejected');
check(resolveFileBelow($temporaryRoot . DIRECTORY_SEPARATOR . 'downloads', '..\\secret.txt') === null, 'Windows parent traversal should be rejected');
check(resolveFileBelow($temporaryRoot . DIRECTORY_SEPARATOR . 'downloads', $temporaryRoot . DIRECTORY_SEPARATOR . 'secret.txt') === null, 'absolute path should be rejected');

unlink($temporaryRoot . DIRECTORY_SEPARATOR . 'downloads' . DIRECTORY_SEPARATOR . 'allowed.zip');
rmdir($temporaryRoot . DIRECTORY_SEPARATOR . 'downloads');
unlink($temporaryRoot . DIRECTORY_SEPARATOR . 'secret.txt');
rmdir($temporaryRoot);

list($body) = runEndpoint('getcontent.php', array(
    'content' => 'packageinfo',
    'callback' => 'x;window.pwned()',
));
check($body === 'Invalid callback parameter.', 'invalid JSONP callback should fail closed');

list($body) = runEndpoint('getcontent.php', array(
    'content' => 'packageinfo',
    'callback' => 'client.done',
));
check(str_starts_with($body, 'client.done(') && str_ends_with($body, ')'), 'valid JSONP wrapper should be preserved');
$json = substr($body, strlen('client.done('), -1);
check(is_array(json_decode($json, true)), 'JSONP payload should contain valid JSON');

list($body) = runEndpoint('getcontent.php', array(
    'content' => 'filelist',
    'file' => '<img src=x onerror=alert(1)>.zip',
), array('HTTP_HOST' => 'evil.example\"><script>alert(1)</script>'));
check(strpos($body, '<img src=x onerror=alert(1)>') === false, 'file list should HTML-escape the requested name');
check(strpos($body, 'evil.example') === false, 'file list should not reflect an untrusted Host header');

list($body) = runEndpoint('getremote.php', array('src' => 'http://127.0.0.1/private'));
check(strpos($body, 'not available') !== false, 'getremote should block private-network SSRF');

list($body) = runEndpoint('download.php', array('file' => '../README.md'));
check($body === "The specified file doesn't exist on the server", 'download traversal should use the existing not-found response');

list($body) = runEndpoint('packageinfo.php', array('file' => '<img src=x onerror=alert(1)>.zip'));
check(strpos($body, '<img src=x onerror=alert(1)>') === false, 'package page should escape the requested name');
check(strpos($body, '&lt;img src=x onerror=alert(1)&gt;.zip') !== false, 'package page should retain the escaped requested name');

if ($failures !== array()) {
    foreach ($failures as $failure) {
        fwrite(STDERR, "FAIL: " . $failure . PHP_EOL);
    }
    exit(1);
}

echo 'Security regression tests passed.' . PHP_EOL;
