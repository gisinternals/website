<?php
require_once __DIR__ . '/includes/security.php';

$fileName = requestString($_GET, 'file');
if ($fileName === null) {
    die('Missing file parameter');
}

$fullPath = resolveFileBelow(__DIR__ . '/sdk/downloads', $fileName);
if ($fullPath === null) {
    die("The specified file doesn't exist on the server");
}

$downloadName = basename(str_replace('\\', '/', $fileName));
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . addcslashes($downloadName, "\\\"") . '"; filename*=UTF-8\'\'' . rawurlencode($downloadName));
header('X-Content-Type-Options: nosniff');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($fullPath));
readfile($fullPath);
exit;
?>
