<?

include "include/lib.php";

// Get some project specifics
$projectparams = getprojectconfig();

if (!strstr($_SERVER["HTTP_REFERER"],$projectparams['GITURL'])) {
	// Go back to diffmanager
        moveto("admin.php?part=4");
        return;
}

dbconnect();

$query = "SELECT `diff_filename` FROM `diffmanager` WHERE `no` = '".$_GET['no']."'";
$filename = mysql_result(mysql_query($query),0,0);
if (file_exists($projectparams['DIFFDIR']."/".$filename)) {
	unlink($projectparams['DIFFDIR']."/".$filename);
}
$query = "DELETE FROM `diffmanager` WHERE `no` = '".$_GET['no']."'";
mysql_query($query);

dbclose();

// Go back to diffmanager
moveto("admin.php?part=4");
?>
