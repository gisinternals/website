<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<!-- #BeginTemplate "three-column.dwt" -->

<head>
<!-- #BeginEditable "doctitle" -->
<title>GISInternals Support Site</title>
<!-- #EndEditable -->
<!-- #BeginEditable "headsection" -->
<meta name="google-site-verification" content="Gi4-YUWoR0q-DbmKfHOdllJUqMGUHEBV9kb6IT0Nz3I" />
<meta content="GISInternals Support Site" name="description" />
<meta content="GISInternals SDK GDAL MapServer" name="keywords" />
<!-- #EndEditable -->
<meta content="text/html; charset=iso-8859-2" http-equiv="Content-Type" />
<meta content="hu-hu" http-equiv="Content-Language" />
<meta content="General" name="rating" />
<meta content="no" http-equiv="imagetoolbar" />
<meta content="Copyright &copy; 2018, Tamas Szekeres, all rights reserved" name="copyright" />
<!-- #BeginEditable "includestyles" -->
<link href="styles/three-column-layout.css" rel="stylesheet" type="text/css" />
<!-- #EndEditable -->
<link rel="shortcut icon" href="favicon.ico"/>
<!-- #BeginEditable "includescripts" -->
<script type="text/javascript" src="https://code.jquery.com/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="scripts/gisinternals.js"></script>
<?php
function GetMsRevInfo($id) {
    $sdkRoot = "./sdk/";
    $revisionfile = $sdkRoot."downloads/doc/".$id."/ms_revision.txt";
	if (file_exists($revisionfile)) {
        $contents = file_get_contents($revisionfile);
        if (preg_match("/(?<=revision\s)[0-9]+/i", $contents, $matches)) {
            return "<a href=\"http://trac.osgeo.org/mapserver/changeset/".$matches[0]."\">r".$matches[0]."</a>";
        }
        else {
            if (strlen($contents) == 40)
               return "<a href=\"https://github.com/mapserver/mapserver/commit/".$contents."\">".substr($contents, 0,7)."</a>";
        }
    }
    return "";
}
function GetGdalRevInfo($id) {
    $sdkRoot = "./sdk/";
    $revisionfile = $sdkRoot."downloads/doc/".$id."/gdal_revision.txt";
	if (file_exists($revisionfile)) {
        $contents = file_get_contents($revisionfile);
        if (preg_match("/(?<=revision\s)[0-9]+/i", $contents, $matches)) {
            return "<a href=\"http://trac.osgeo.org/gdal/changeset/".$matches[0]."\">r".$matches[0]."</a>";
        }
        else {
            if (strlen($contents) == 40)
               return "<a href=\"https://github.com/OSGeo/gdal/commit/".$contents."\">".substr($contents, 0,7)."</a>";
        }
    }
    return "";
}
function GetFileDate($fileName) {
    $sdkRoot = "./sdk/";
	if (file_exists($sdkRoot."downloads/".$fileName)) {
        $fullPath = $sdkRoot."downloads/".$fileName;
        return date("Y-m-d H:i:s", filectime($fullPath));
    }
}
function GetPackageHTML($fileName) {
    $dirName = basename($fileName, ".zip");
    return "<td>".GetFileDate($fileName)."</td><td>".GetMsRevInfo($dirName)." ".GetGdalRevInfo($dirName)."</td>";
}
?>
<!-- #EndEditable -->
</head>

<body id="mainbody">

<div id="container">
    <div id="logoback">
	<div id="logoleft">
	<img alt="" src="images/ban-title.png" style="float: right"/>
	</div>
	</div>
	<div id="topmenu">
		<ul>
			<li><a href="index.html" title="Home">Home</a></li>
			<li><a href="aboutgisinternals.html" title="About">About</a></li>
			<li><a href="kb.html" title="Documents">Documents</a></li>
			<li><a href="licensing.html" title="Licensing">Licensing</a></li>
			<li><img class="mailimage" alt="" src="images/sendmail.png" /><a href="mailto:gisinternals@gisinternals.com" title="Send email">Send email</a></li>
			<li><img class="mailimage" width="16" height="16" alt="" src="https://www.blogger.com/favicon.ico" /><a target="_blank" href="http://blog.gisinternals.com" title="WebLog">WebLog</a></li>
		</ul>
	</div>
	<div class="urbangreymenu">
		<!-- #BeginEditable "leftnavigation" -->
		<!--webbot bot="Include" u-include="includes/left-menu.html" tag="BODY" startspan -->

<p class="headerbar"><img src="favicon.ico"/>About</p>
<ul>
<li><a href="aboutgisinternals.html" title="About This Site">About This Site</a></li>
<li><a href="myprofile.html" title="Maintenance">Maintenance</a></li>
<li><a href="sponsors.html" title="Sponsors">Sponsors</a></li>
<li><a href="contact.html" title="Contact Info">Contact Info</a></li>
<li><a href="projects.html" title="Projects">Projects</a></li>
<li><a href="issues.html" title="Issue Tracking">Issue Tracking</a></li>
</ul>
<p class="headerbar"><img src="favicon.ico"/>Downloads</p>
<ul>
<li><a href="release.php" title="Stable Releases">Stable Releases</a></li>
<li><a href="stable.php" title="Stable Branches (daily duilds)">Stable Branches (daily)</a></li>
<li><a href="development.php" title="Development Versions (daily builds)">Development Versions (daily)</a></li>
<li><a href="sdk.php" title="Development Kits">Development Kits</a></li>
<li><a href="archive.php" title="Older Releases">Older Releases</a></li>
<li><a href="mapmanager.html" title="Mapserver MapManager"><img style="padding-left:0px;margin-left:0px" alt="MapManager" src="images/mapmanager.png"/>Mapserver MapManager</a></li>

</ul>

<p class="headerbar"><img src="favicon.ico"/>Build Status</p>
<ul>
<li><a href="status.php" title="Latest Build Status">Latest Build Status</a></li>

</ul>
<p class="headerbar"><img src="favicon.ico"/>Documentation</p>
<ul>
<li><a target="_blank" href="http://www.gisinternals.com/MapManager/" title="MapServer MapManager"><img style="padding-left:0px;margin-left:0px" alt="MapManager" src="images/mapmanager.png"/>MapServer MapManager</a></li>
<li><a href="kb.html" title="Documents">Documents</a></li>
<li><a href="changes.html" title="Change Log">Change Log</a></li>
<li><a href="licensing.html" title="License Information">License Information</a></li>
<li><a href="links.html" title="Related Links">Related Links</a></li>
</ul>
<div id="support">
<p style="color:green;font-weight:bold">Support GISInternals</p>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick"/>
<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
">
<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" name="submit" alt="PayPal - The safer, easier way to pay online!"/>
<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1"/>
</form>
</div>
<!--webbot bot="Include" endspan i-checksum="181" --><!-- #EndEditable --></div>
	<div id="rightnav">
		<!-- #BeginEditable "rightsidebar" -->
		<!--webbot bot="Include" u-include="includes/rightbar-main.html" tag="BODY" startspan -->
<div class="news">
<p class="header"><img src="favicon.ico"/>News</p>

<div class="newssection">
<h2>Events</h2>
<a target="_blank" href="https://2020.europe.foss4g.org/"><img width="130"  src="content-images/foss4g-europe-2020.svg" alt="FOSS4G Europe 2020"/></a>
<br/><br/>
<a target="_blank" href="https://2020.foss4g.org/"><img width="130" alt="FOSS4G 2020" src="content-images/FOS_HeaderLogo_2x.png" /></a>

</div>
<!--div class="newssection">
<a target="_blank" href="http://europe.foss4g.org/2015/"><img width="130"  src="../content-images/foss4ge2015.png" alt="FOSS4G Europe 2015"/></a>
</div-->

<!--div id="support">
<p style="color:green;font-weight:bold">Support GISInternals</p>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick"/>
<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
">
<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" name="submit" alt="PayPal - The safer, easier way to pay online!"/>
<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1"/>
</form>
</div-->
</div>
<div class="logos">
<a target="_blank" href="http://www.osgeo.org"><img alt="Open Source Geospatial Foundation" width="160" src="content-images/osgeo.png" style="border: 1px solid #C0C0C0;" /></a>
<br/>
<a target="_blank" href="http://www.mapserver.org"><img alt="UMN MapServer" width="160" src="content-images/mapserver.png" style="border: 1px solid #C0C0C0;" /></a>
<br/>
<a target="_blank" href="http://www.gdal.org"><img alt="Geospatial Data Abstraction Library" width="70" src="content-images/gdal.png" style="border: 1px solid #C0C0C0;" /></a>


</div>
<!--webbot bot="Include" endspan i-checksum="35518" --><!-- #EndEditable --></div>
	<div id="content">
		<!-- #BeginEditable "maincontent" -->
		<h1>Development versions</h1>
		
		<p>The following packages are compiled daily based on the MapServer Git (main) and GDAL Git (master). This is based on the development branches containing all the latest features added to the projects. It is recommended to use these packages mainly for testing purposes.</p>
		<table>
		<tr><th>Compiler</th><th>Arch.</th><th>Downloads</th><th>Package Info</th><th>Date</th><th>Revisions</th></tr>
		<tr><td>MSVC 2017 <sup class="note">new</sup></td><td>win32</td><td><a href="query.html?content=filelist&file=release-1916-gdal-mapserver.zip">release-1916-gdal-mapserver</a></td><td><a href="packageinfo.php?file=release-1916-gdal-mapserver.zip">information</a></td><?php echo GetPackageHTML("release-1916-gdal-mapserver.zip") ?></tr>
		<tr><td>MSVC 2017 <sup class="note">new</sup></td><td>x64</td><td><a href="query.html?content=filelist&file=release-1916-x64-gdal-mapserver.zip">release-1916-x64-gdal-mapserver</a></td><td><a href="packageinfo.php?file=release-1916-x64-gdal-mapserver.zip">information</a></td><?php echo GetPackageHTML("release-1916-x64-gdal-mapserver.zip") ?></tr>
		<tr><td>MSVC 2019 <sup class="note">new</sup></td><td>win32</td><td><a href="query.html?content=filelist&file=release-1928-gdal-mapserver.zip">release-1928-gdal-mapserver</a></td><td><a href="packageinfo.php?file=release-1928-gdal-mapserver.zip">information</a></td><?php echo GetPackageHTML("release-1928-gdal-mapserver.zip") ?></tr>
		<tr><td>MSVC 2019 <sup class="note">new</sup></td><td>x64</td><td><a href="query.html?content=filelist&file=release-1928-x64-gdal-mapserver.zip">release-1928-x64-gdal-mapserver</a></td><td><a href="packageinfo.php?file=release-1928-x64-gdal-mapserver.zip">information</a></td><?php echo GetPackageHTML("release-1928-x64-gdal-mapserver.zip") ?></tr>
		<tr><td>MSVC 2022 <sup class="note">new</sup></td><td>win32</td><td><a href="query.html?content=filelist&file=release-1930-gdal-mapserver.zip">release-1930-gdal-mapserver</a></td><td><a href="packageinfo.php?file=release-1930-gdal-mapserver.zip">information</a></td><?php echo GetPackageHTML("release-1930-gdal-mapserver.zip") ?></tr>
		<tr><td>MSVC 2022 <sup class="note">new</sup></td><td>x64</td><td><a href="query.html?content=filelist&file=release-1930-x64-gdal-mapserver.zip">release-1930-x64-gdal-mapserver</a></td><td><a href="packageinfo.php?file=release-1930-x64-gdal-mapserver.zip">information</a></td><?php echo GetPackageHTML("release-1930-x64-gdal-mapserver.zip") ?></tr>
		</table>
		<p><span class="note">Note:</span> The different compilers or architectures involve different CRT dependencies, therefore the binaries of the different packages are not interchangeable.</p>
<p>The <span class="note">new</span> packages are based on a completely new SDK build with upgraded dependency versions.</p>
<p>The contents of the packages are provided under the terms of <a href="licensing.html">this license</a>. It is intended to give you permission to do whatever you want with the files: download, modify, redistribute as you please, including building proprietary commercial software, no permission from <a href="myprofile.html">Tamas Szekeres</a> is required. Some external libraries which can be optionally used by GDAL and MapServer (provided as plugins) are under radically different licenses, you <strong>MUST obtain valid licenses</strong> for each of these dependent libraries.</p>

		<!-- #EndEditable -->
		</div>
	<div id="footer">
		<p>GISInternals | <a href="mailto:gisinternals@gisinternals.com" title="Mail to GISInternals">gisinternals@gisinternals.com</a></p>
		<p>Copyright &copy; 2021 | All Rights Reserved</p>
	</div>
</div>
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker("UA-13269500-1");
pageTracker._trackPageview();
} catch(err) {}</script>
</body>

<!-- #EndTemplate -->

</html>
