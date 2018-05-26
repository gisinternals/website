<?php
if (isset($_GET['src']) or die("src parameter missing.")) {
    $result = file_get_contents($_GET['src']);
    if ($result == false)
    	$result = "<span style=\"background-color:Gray;color:Black;font-weight:bold\">not available</span>";
    echo $result;
}
?>