<?php
require_once __DIR__ . '/includes/security.php';

function GetFileSize($fullPath) {
	return round(filesize($fullPath)/1024);
}

function GetDescription($fileName) {
	if (preg_match("/gdal-.*-core\.msi/i", $fileName))
		return "Generic installer for the GDAL core components";
	if (preg_match("/mapserver-.*-core\.msi/i", $fileName))
		return "MapServer installer with IIS registration support";
	if (preg_match("/MapManager.*\.msi/i", $fileName))
		return "MapServer MapManager installer";
	if (preg_match("/gdal-.*-ecw\.msi/i", $fileName))
		return "Installer for the GDAL ECW plugin (must be installed to the same directory as the GDAL core)";
	if (preg_match("/gdal-.*-ecw-33\.msi/i", $fileName))
		return "Installer for the GDAL ECW 3.3 plugin (must be installed to the same directory as the GDAL core, the 3.3 and 5.x versions cannot be installed side by side)";
	if (preg_match("/gdal-.*-ecw-51\.msi/i", $fileName))
		return "Installer for the GDAL ECW 5.1 plugin (must be installed to the same directory as the GDAL core, the 3.3 and 5.x versions cannot be installed side by side)";
    if (preg_match("/gdal-.*-ecw-53\.msi/i", $fileName))
		return "Installer for the GDAL ECW 5.3 plugin (must be installed to the same directory as the GDAL core, the 3.3 and 5.x versions cannot be installed side by side)";
    if (preg_match("/gdal-.*-ecw-55\.msi/i", $fileName))
		return "Installer for the GDAL ECW 5.5 plugin (must be installed to the same directory as the GDAL core, the 3.3 and 5.x versions cannot be installed side by side)";
	if (preg_match("/gdal-.*-oracle\.msi/i", $fileName))
		return "Installer for the GDAL Oracle plugin (must be installed to the same directory as the GDAL core)";
	if (preg_match("/gdal-.*-filegdb\.msi/i", $fileName))
		return "Installer for the GDAL FileGDB plugin (must be installed to the same directory as the GDAL core)";
	if (preg_match("/gdal-.*-mrsid\.msi/i", $fileName))
		return "Installer for the GDAL MrSID plugin (must be installed to the same directory as the GDAL core)";
	if (preg_match("/GDAL-.*py.*\.(exe|msi)/i", $fileName))
		return "Installer for the GDAL python bindings (requires to install the GDAL core)";
	if (preg_match("/GDAL-.*Python.*\.zip/i", $fileName))
		return "GDAL python bindings in a .zip package (requires to install the GDAL core)";
	if (preg_match("/MapScript-.*py.*\.(exe|msi)/i", $fileName))
		return "Installer for the MapScript python bindings (Not yet working)";
	return "";
}

function ProcessFilelist() {
    $downloadRoot = __DIR__ . "/sdk/downloads";
    $fileName = requestString($_GET, 'file');
    if ($fileName === null) {
        die("Missing file parameter");
    }
    if (!isSafeLeafName($fileName)) {
        die("Content not available at server.");
    }

    $extensionPosition = strrpos($fileName, ".");
    $dirName = $extensionPosition === false ? $fileName : substr($fileName, 0, $extensionPosition);
    if (!isSafeLeafName($dirName)) {
        die("Content not available at server.");
    }

		$baseUrl = "https://" . safePublicHost($_SERVER) . "/";
		echo "<p>Available downloads (<b>" . escapeHtml($dirName) . "</b>):</p><table>";
		echo "<tr><th>File name</th><th>File date</th><th>Size</th><th>Description</th>";
		$fullPath = resolveFileBelow($downloadRoot, $fileName);
		if ($fullPath !== null) {
			echo "<tr><td><a href=\"" . $baseUrl . "sdk/downloads/" . rawurlencode($fileName) . "\">" . escapeHtml($fileName) . "</a></td><td>" . date("Y-m-d H:i:s", filemtime($fullPath)) . "</td><td>" . GetFileSize($fullPath) . " kB</td><td>Compiled binaries in a single .zip package</td></tr>";
		}
		$sourceName = $dirName . "-src.zip";
		$fullPath = resolveFileBelow($downloadRoot, $sourceName);
		if ($fullPath !== null) {
			echo "<tr><td><a href=\"" . $baseUrl . "sdk/downloads/" . rawurlencode($sourceName) . "\">" . escapeHtml($sourceName) . "</a></td><td>" . date("Y-m-d H:i:s", filemtime($fullPath)) . "</td><td>" . GetFileSize($fullPath) . " kB</td><td>GDAL and MapServer sources</td></tr>";
		}
		$libraryName = $dirName . "-libs.zip";
		$fullPath = resolveFileBelow($downloadRoot, $libraryName);
		if ($fullPath !== null) {
			echo "<tr><td><a href=\"" . $baseUrl . "sdk/downloads/" . rawurlencode($libraryName) . "\">" . escapeHtml($libraryName) . "</a></td><td>" . date("Y-m-d H:i:s", filemtime($fullPath)) . "</td><td>" . GetFileSize($fullPath) . " kB</td><td>Compiled libraries and headers</td></tr>";
		}
		$pkgDir = realpath($downloadRoot . DIRECTORY_SEPARATOR . $dirName);
		$downloadRootPath = realpath($downloadRoot);
		if ($pkgDir !== false && $downloadRootPath !== false && is_dir($pkgDir)
            && strncmp($pkgDir, rtrim($downloadRootPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR, strlen(rtrim($downloadRootPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR)) === 0) {
			$dir = new DirectoryIterator($pkgDir);
			foreach ($dir as $fileinfo) {
				if (!$fileinfo->isDot() && $fileinfo->isFile()) {
                    $entryName = $fileinfo->getFilename();
					$fullPath = $fileinfo->getPathname();
					echo "<tr><td><a href=\"" . $baseUrl . "sdk/downloads/" . rawurlencode($dirName) . "/" . rawurlencode($entryName) . "\">" . escapeHtml($entryName) . "</a></td><td>" . date("Y-m-d H:i:s", filemtime($fullPath)) . "</td><td>" . GetFileSize($fullPath) . " kB</td><td>" . escapeHtml(GetDescription($entryName)) . "</td></tr>";
				}
			}
			echo "</table><p><span class=\"note\">Note:</span> In order to have the bindings work the location of the core components must be included manually in the PATH environment variable.</p>";
		}
		else
			echo "</table>";		
}
function ProcessPackageinfo() { 
	echo "This page is under construction. Please refer to the <a target=\"_blank\" href=\"https://archive.gisinternals.com/sdk/\">build server page</a> for the status information";
}
function ProcessChangeLog() { 
	echo "This page is under construction. Please refer to the <a target=\"_blank\" href=\"https://archive.gisinternals.com/sdk/\">build server page</a> for the status information";
}
function ProcessBuildLog() { 
	echo "This page is under construction. Please refer to the <a target=\"_blank\" href=\"https://archive.gisinternals.com/sdk/\">build server page</a> for the status information";
}
function ProcessBuildStatus() { 
	echo "This page is under construction. Please refer to the <a target=\"_blank\" href=\"https://archive.gisinternals.com/sdk/\">build server page</a> for the status information";
}
function GetRemote($url) { 
	$result = fetchAllowedRemote($url);
    if ($result == false)
    	$result = "<span style=\"background-color:Gray;color:Black;font-weight:bold\">not available</span>";
    echo $result;
}

function ProcessArchive() { 
	echo "This page is under construction. Please refer to the <a target=\"_blank\" href=\"https://archive.gisinternals.com/sdk/\">build server page</a> for the status information";
}
function ProcessText() { 
	echo "This page is under construction. Please refer to the <a target=\"_blank\" href=\"https://archive.gisinternals.com/sdk/\">build server page</a> for the status information";
}
function ProcessMSAutotest() { 
	echo "This page is under construction. Please refer to the <a target=\"_blank\" href=\"https://archive.gisinternals.com/sdk/\">build server page</a> for the status information";
}
$callback = requestString($_GET, 'callback');
if ($callback !== null && !isValidJsonpCallback($callback)) {
    http_response_code(400);
    header('Content-Type: text/plain; charset=UTF-8');
    die('Invalid callback parameter.');
}

$content = requestString($_GET, 'content');
if ($content === null) {
    die("content parameter missing.");
}

ob_start();
switch ($content) {
    	case "filelist":
            ProcessFilelist();
            break;
        case "packageinfo":
            ProcessPackageinfo();
            break;
        case "changelog":
            ProcessChangeLog();
            break;           
        case "buildlog":
            ProcessBuildLog();
            break;
        case "status":
            ProcessBuildStatus();
            break;
        case "getremote":
            $source = requestString($_GET, 'src');
            if ($source === null) {
                die('src parameter missing.');
            }
            GetRemote($source);
            break;
        case "archive":
            ProcessArchive();
            break;
        case "text":
            ProcessText();
            break;
        case "msautotest":
            ProcessMSAutotest();
            break;  
        default:
            die("Content not available at server.");
            break;    
}
$html = ob_get_clean();
$payload = json_encode(array('html' => $html), JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);

if ($callback !== null) {
    header('Content-Type: application/javascript; charset=UTF-8');
    echo $callback . '(' . $payload . ')';
} else {
    header('Content-Type: application/json; charset=UTF-8');
    echo $payload;
}
?>
