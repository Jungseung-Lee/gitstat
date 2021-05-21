<?

include "include/lib.php";

// Get some project specifics
$projectparams = getprojectconfig();

//We should only get here via admin.php
if (!strstr($_SERVER["HTTP_REFERER"],$projectparams['GITURL'])) {
	// Go back to diffmanager
	moveto("admin.php?part=4");
        return;
}

dbconnect();

if ($_POST['fsize'] && $_POST['fcount']) {
	$_POST['fsize'] *= 1024;
	$query = "SELECT * FROM `diffmanager` WHERE `diff_filesize` <= ".$_POST['fsize']." ADN `diff_count` <= ".$_POST['fcount'];
} else if ($_POST['fsize'] && !$_POST['fcount']) {
	$_POST['fsize'] *= 1024;
	$query="SELECT * FROM `diffmanager` WHERE `diff_filesize` <= ".$_POST['fsize'];
} else {
	$query="SELECT * FROM `diffmanager` WHERE `count` <= ".$_POST['fcount'];
}

$result = mysql_query($query);
while ($data = mysql_fetch_array($result)) {
	if (file_exists($projectparams['DIFFDIR']."/".$data['diff_filename'])) {
		unlink($projectparams['DIFFDIR']."/".$data['diff_filename']);
	}
	$query = "DELETE FROM `diffmanager` WHERE `no` = '".$data['no']."'";
	mysql_query($query);
}

dbclose();

// Go back to diffmanager
moveto("admin.php?part=4");
?>
