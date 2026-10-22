<?php
require_once __DIR__ . '/includes/security.php';

$source = requestString($_GET, 'src');
if ($source === null) {
    die('src parameter missing.');
}

$result = fetchAllowedRemote($source);
if ($result === false) {
    $result = '<span style="background-color:Gray;color:Black;font-weight:bold">not available</span>';
}

echo $result;
?>
