<?php
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
	if (preg_match("/gdal-.*-oracle\.msi/i", $fileName))
		return "Installer for the GDAL Oracle plugin (must be installed to the same directory as the GDAL core)";
	if (preg_match("/gdal-.*-filegdb\.msi/i", $fileName))
		return "Installer for the GDAL FileGDB plugin (must be installed to the same directory as the GDAL core)";
	if (preg_match("/gdal-.*-mrsid\.msi/i", $fileName))
		return "Installer for the GDAL MrSID plugin (must be installed to the same directory as the GDAL core)";
	if (preg_match("/GDAL-.*py.*\.(exe|msi)/i", $fileName))
		return "Installer for the GDAL python bindings (requires to install the GDAL core)";
	if (preg_match("/MapScript-.*py.*\.(exe|msi)/i", $fileName))
		return "Installer for the MapScript python bindings (Not yet working)";
	return "";
}

function ProcessFilelist() {
    $sdkRoot = "./sdk/"; 
	if (isset($_GET['file']) or die ("Missing file parameter")) {
	    $fileName = $_GET['file'];
		$dirName = substr($fileName, 0, strrpos($fileName, "."));
		$baseUrl = "http://".$_SERVER['HTTP_HOST']."/";
		echo "<p>Available downloads (<b>".$dirName."</b>):</p><table>";
		echo "<tr><th>File name</th><th>File date</th><th>Size</th><th>Description</th>";
		if (file_exists($sdkRoot."downloads/".$fileName)) {
		    $fullPath = $sdkRoot."downloads/".$fileName;
			echo "<tr><td><a href=\"".$baseUrl."sdk/downloads/".$fileName."\">".$fileName."</a></td><td>".date("Y-m-d H:i:s", filectime($fullPath))."</td><td>".GetFileSize($fullPath)." kB</td><td>Compiled binaries in a single .zip package</td></tr>";
		}
		if (file_exists($sdkRoot."downloads/".$dirName."-src.zip")) {
		    $fullPath = $sdkRoot."downloads/".$dirName."-src.zip";
			echo "<tr><td><a href=\"".$baseUrl."sdk/downloads/".$dirName."-src.zip\">".$dirName."-src.zip</a></td><td>".date("Y-m-d H:i:s", filectime($fullPath))."</td><td>".GetFileSize($fullPath)." kB</td><td>GDAL and MapServer sources</td></tr>";
		}
		if (file_exists($sdkRoot."downloads/".$dirName."-libs.zip")) {
		    $fullPath = $sdkRoot."downloads/".$dirName."-libs.zip";
			echo "<tr><td><a href=\"".$baseUrl."sdk/downloads/".$dirName."-libs.zip\">".$dirName."-libs.zip</a></td><td>".date("Y-m-d H:i:s", filectime($fullPath))."</td><td>".GetFileSize($fullPath)." kB</td><td>Compiled libraries and headers</td></tr>";
		}
		$pkgDir = $sdkRoot."downloads/".$dirName;
		if (file_exists($pkgDir)) {
			$dir = new DirectoryIterator($pkgDir);
			foreach ($dir as $fileinfo) {
	    		if (!$fileinfo->isDot()) {
	    		    $fullPath = $sdkRoot."downloads/".$dirName."/".$fileinfo->getFilename();
	    			echo "<tr><td><a href=\"".$baseUrl."sdk/downloads/".$dirName."/".$fileinfo->getFilename()."\">".$fileinfo->getFilename()."</a></td><td>".date("Y-m-d H:i:s", filectime($fullPath))."</td><td>".GetFileSize($fullPath)." kB</td><td>".GetDescription($fileinfo->getFilename())."</td></tr>";
	    		}
			}
			echo "</table><p><span class=\"note\">Note:</span> In order to have the bindings work the location of the core components must be included manually in the PATH environment variable.</p>";
		}
		else
			echo "</table>";		
	}
}
function ProcessPackageinfo() { 
	echo "This page is under construction. Please refer to the <a target=\"_blank\" href=\"http://archive.gisinternals.com/sdk/\">build server page</a> for the status information";
}
function ProcessChangeLog() { 
	echo "This page is under construction. Please refer to the <a target=\"_blank\" href=\"http://archive.gisinternals.com/sdk/\">build server page</a> for the status information";
}
function ProcessBuildLog() { 
	echo "This page is under construction. Please refer to the <a target=\"_blank\" href=\"http://archive.gisinternals.com/sdk/\">build server page</a> for the status information";
}
function ProcessBuildStatus() { 
	echo "This page is under construction. Please refer to the <a target=\"_blank\" href=\"http://archive.gisinternals.com/sdk/\">build server page</a> for the status information";
}
function GetRemote($url) { 
	$result = file_get_contents($url);
    if ($result == false)
    	$result = "<span style=\"background-color:Gray;color:Black;font-weight:bold\">not available</span>";
    echo $result;
}

function ProcessArchive() { 
	echo "This page is under construction. Please refer to the <a target=\"_blank\" href=\"http://archive.gisinternals.com/sdk/\">build server page</a> for the status information";
}
function ProcessText() { 
	echo "This page is under construction. Please refer to the <a target=\"_blank\" href=\"http://archive.gisinternals.com/sdk/\">build server page</a> for the status information";
}
function ProcessMSAutotest() { 
	echo "This page is under construction. Please refer to the <a target=\"_blank\" href=\"http://archive.gisinternals.com/sdk/\">build server page</a> for the status information";
}
header('Content-type: application/json');
if (isset($_GET['callback'])) {
	echo $_GET['callback'];
	echo "(";
}
echo "{'html': '";
if (isset($_GET['content']) or die("content parameter missing.")) {
    switch ($_GET['content']) {
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
            GetRemote($_GET['src']);
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
}
echo "'}";
if (isset($_GET['callback'])) {
	echo ")";
}
?>