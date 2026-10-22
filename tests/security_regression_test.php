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

function startTestServer(): array
{
    $socket = stream_socket_server('tcp://127.0.0.1:0', $errorNumber, $errorMessage);
    if ($socket === false) {
        throw new RuntimeException($errorMessage, $errorNumber);
    }
    $address = stream_socket_get_name($socket, false);
    fclose($socket);
    $port = (int) substr($address, strrpos($address, ':') + 1);

    $command = array(
        PHP_BINARY,
        '-S',
        '127.0.0.1:' . $port,
        __DIR__ . '/fixtures/redirect_router.php',
    );
    $descriptors = array(
        0 => array('pipe', 'r'),
        1 => array('pipe', 'w'),
        2 => array('pipe', 'w'),
    );
    $process = proc_open($command, $descriptors, $pipes, realpath(__DIR__ . '/..'));
    if (!is_resource($process)) {
        throw new RuntimeException('Unable to start the local redirect test server.');
    }

    $ready = false;
    for ($attempt = 0; $attempt < 50; ++$attempt) {
        $connection = @fsockopen('127.0.0.1', $port, $errorNumber, $errorMessage, 0.1);
        if (is_resource($connection)) {
            fclose($connection);
            $ready = true;
            break;
        }
        usleep(20000);
    }
    if (!$ready) {
        proc_terminate($process);
        throw new RuntimeException('Local redirect test server did not start.');
    }

    return array($process, $pipes, $port);
}

function stopTestServer($process, array $pipes): void
{
    proc_terminate($process);
    foreach ($pipes as $pipe) {
        fclose($pipe);
    }
    proc_close($process);
}

check(isValidJsonpCallback('jQuery1720123_456'), 'jQuery callback should be accepted');
check(isValidJsonpCallback('client.callbacks.done'), 'dotted callback should be accepted');
check(!isValidJsonpCallback('alert(1)//'), 'executable callback should be rejected');
check(!isValidJsonpCallback('x;window.pwned()'), 'statement callback should be rejected');

$publicResolver = static function (string $host): array {
    return array(array('host' => $host, 'ip' => '1.1.1.1'));
};

check(!isAllowedRemoteUrl('file:///etc/passwd', $publicResolver), 'file URL should be rejected');
check(!isAllowedRemoteUrl('http://127.0.0.1/admin', $publicResolver), 'IP hostname should be rejected');
check(!isAllowedRemoteUrl('http://169.254.169.254/latest/meta-data', $publicResolver), 'metadata hostname should be rejected');
check(!isAllowedRemoteUrl('https://gisinternals.com.evil.example/', $publicResolver), 'lookalike domain should be rejected');
check(!isAllowedRemoteUrl('https://www.gisinternals.com/', $publicResolver), 'non-proxy GISInternals host should be rejected');
check(!isAllowedRemoteUrl('https://download.gisinternals.com./', $publicResolver), 'non-canonical trailing-dot host should be rejected');
check(!isAllowedRemoteUrl('https://user@download.gisinternals.com/', $publicResolver), 'credential-bearing URL should be rejected');
check(!isAllowedRemoteUrl('https://download.gisinternals.com:444/', $publicResolver), 'unsupported HTTPS port should be rejected');
check(!isAllowedRemoteUrl('http://download.gisinternals.com:443/', $publicResolver), 'scheme-mismatched port should be rejected');
check(isAllowedRemoteUrl('https://download.gisinternals.com/sdk/status.txt', $publicResolver), 'download host should be accepted');
check(isAllowedRemoteUrl('https://build2.gisinternals.com/sdk/status.txt', $publicResolver), 'build2 host should be accepted');

$privateAddresses = array(
    '0.0.0.0',
    '10.0.0.1',
    '127.0.0.1',
    '169.254.169.254',
    '172.16.0.1',
    '192.168.0.1',
    '::',
    '::1',
    '::ffff:127.0.0.1',
    'fc00::1',
    'fd00::1',
    'fe80::1',
);
foreach ($privateAddresses as $address) {
    check(!isPublicIp($address), $address . ' should not be treated as public');
}
check(isPublicIp('1.1.1.1'), 'public IPv4 should be accepted');
check(isPublicIp('2606:4700:4700::1111'), 'public IPv6 should be accepted');

$mixedResolver = static function (string $host): array {
    return array(
        array('host' => $host, 'ip' => '1.1.1.1'),
        array('host' => $host, 'ip' => '127.0.0.1'),
    );
};
check(resolvePublicIps('download.gisinternals.com', $mixedResolver) === null, 'mixed public/private DNS answers should fail closed');

$resolutionCount = 0;
$connectedIp = null;
$rebindingResolver = static function (string $host) use (&$resolutionCount): array {
    ++$resolutionCount;
    return $resolutionCount === 1
        ? array(array('host' => $host, 'ip' => '1.1.1.1'))
        : array(array('host' => $host, 'ip' => '127.0.0.1'));
};
$recordingFetcher = static function (array $target, string $ip) use (&$connectedIp): string {
    $connectedIp = $ip;
    return 'pinned response';
};
check(fetchAllowedRemote('https://download.gisinternals.com/test', $rebindingResolver, $recordingFetcher) === 'pinned response', 'validated address should be passed to the connection');
check($resolutionCount === 1, 'remote fetch should resolve DNS exactly once');
check($connectedIp === '1.1.1.1', 'connection should use the validated DNS address');

check(extension_loaded('curl'), 'ext-curl is required');
if (extension_loaded('curl')) {
    $target = parseAllowedRemoteUrl('https://download.gisinternals.com/test');
    $options = buildPinnedCurlOptions($target, '2606:4700:4700::1111');
    check($options[CURLOPT_FOLLOWLOCATION] === false, 'redirect following should be disabled');
    check($options[CURLOPT_MAXREDIRS] === 0, 'maximum redirects should be zero');
    check($options[CURLOPT_SSL_VERIFYPEER] === true, 'TLS peer verification should be enabled');
    check($options[CURLOPT_SSL_VERIFYHOST] === 2, 'TLS hostname verification should be enabled');
    check($options[CURLOPT_PROTOCOLS] === (CURLPROTO_HTTP | CURLPROTO_HTTPS), 'only HTTP(S) protocols should be enabled');
    check($options[CURLOPT_PROXY] === '' && $options[CURLOPT_NOPROXY] === '*', 'proxy DNS bypass should be disabled');
    check($options[CURLOPT_RESOLVE][0] === 'download.gisinternals.com:443:[2606:4700:4700::1111]', 'IPv6 should be pinned with cURL resolve syntax');

    list($serverProcess, $serverPipes, $serverPort) = startTestServer();
    $localTarget = array(
        'url' => 'http://download.gisinternals.com:' . $serverPort . '/redirect',
        'scheme' => 'http',
        'host' => 'download.gisinternals.com',
        'port' => $serverPort,
    );
    check(fetchPinnedRemote($localTarget, '127.0.0.1') === false, 'redirect response should fail closed without following Location');
    $localTarget['url'] = 'http://download.gisinternals.com:' . $serverPort . '/ok';
    check(fetchPinnedRemote($localTarget, '127.0.0.1') === 'local response', 'pinned HTTP connection should return a successful response');
    stopTestServer($serverProcess, $serverPipes);
}

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

$repositoryRoot = realpath(__DIR__ . '/..');
$downloadsRoot = $repositoryRoot . DIRECTORY_SEPARATOR . 'sdk' . DIRECTORY_SEPARATOR . 'downloads';
$createdDownloadsRoot = false;
if (!is_dir($downloadsRoot)) {
    mkdir($downloadsRoot, 0777, true);
    $createdDownloadsRoot = true;
}
$downloadFixture = $downloadsRoot . DIRECTORY_SEPARATOR . 'security-regression-test.zip';
check(!file_exists($downloadFixture), 'download fixture name should be unused');
file_put_contents($downloadFixture, 'test package bytes');

$docParent = $downloadsRoot . DIRECTORY_SEPARATOR . 'doc';
$createdDocParent = false;
if (!is_dir($docParent)) {
    mkdir($docParent);
    $createdDocParent = true;
}
$docFixture = $docParent . DIRECTORY_SEPARATOR . 'security-regression-test';
check(!file_exists($docFixture), 'document fixture name should be unused');
mkdir($docFixture);
file_put_contents($docFixture . DIRECTORY_SEPARATOR . 'ms_version.txt', "MapServer <script>alert(1)</script>");

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

list($body, $status) = runEndpoint('getcontent.php', array(
    'content' => 'filelist',
    'file' => 'security-regression-test.zip',
    'callback' => 'jQuery1720123_456',
), array('HTTP_HOST' => 'download.gisinternals.com'));
check($status === 0, 'valid file list request should complete');
check(str_starts_with($body, 'jQuery1720123_456('), 'jQuery 1.7 JSONP callback format should be retained');
$fileList = json_decode(substr($body, strlen('jQuery1720123_456('), -1), true);
check(is_array($fileList) && strpos($fileList['html'], 'security-regression-test.zip') !== false, 'existing package should appear in the file list');
check(is_array($fileList) && strpos($fileList['html'], 'https://download.gisinternals.com/sdk/downloads/security-regression-test.zip') !== false, 'file list should retain the existing public download URL');

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

list($body, $status) = runEndpoint('download.php', array('file' => 'security-regression-test.zip'));
check($status === 0 && $body === 'test package bytes', 'valid package download should preserve file contents');

list($body) = runEndpoint('packageinfo.php', array('file' => '<img src=x onerror=alert(1)>.zip'));
check(strpos($body, '<img src=x onerror=alert(1)>') === false, 'package page should escape the requested name');
check(strpos($body, '&lt;img src=x onerror=alert(1)&gt;.zip') !== false, 'package page should retain the escaped requested name');

list($body, $status) = runEndpoint('packageinfo.php', array('file' => 'security-regression-test.zip'));
check($status === 0 && strpos($body, 'MapServer &lt;script&gt;alert(1)&lt;/script&gt;') !== false, 'package document should be retrieved and HTML-escaped');

foreach (array(
    'release.php' => 'Stable Releases',
    'development.php' => 'Development Versions',
    'archive.php' => 'Older Releases',
) as $page => $expectedText) {
    list($body, $status) = runEndpoint($page, array());
    check($status === 0 && strpos($body, $expectedText) !== false, $page . ' should retain its package page output');
}

unlink($docFixture . DIRECTORY_SEPARATOR . 'ms_version.txt');
rmdir($docFixture);
if ($createdDocParent) {
    rmdir($docParent);
}
unlink($downloadFixture);
if ($createdDownloadsRoot) {
    rmdir($downloadsRoot);
}

if ($failures !== array()) {
    foreach ($failures as $failure) {
        fwrite(STDERR, "FAIL: " . $failure . PHP_EOL);
    }
    exit(1);
}

echo 'Security regression tests passed.' . PHP_EOL;
