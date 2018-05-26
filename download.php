<?php
if (isset($_GET['file']) or die ("Missing file parameter")) {
    $fileName = $_GET['file'];
    $fullPath = "./sdk/downloads/".$fileName; 
	if (file_exists($fullPath) or die ("The specified file doesn't exist on the server")) {
	    header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename='.$fileName);
		header('Expires: 0');
    	header('Cache-Control: must-revalidate');
    	header('Pragma: public');
		header('Content-Length: '.filesize($fullPath));
		readfile($fullPath);
		exit;
    }
}
?>