<?
include "include/lib.php";

// Get some project specifics
$giturl = getprojectconfig("GITURL");

if (!strstr($_SERVER["HTTP_REFERER"],$giturl)) {
	echo "<meta http-equiv='refresh' content='1;url=./index.php'>You're not allowed to access this page directly";
	return;
}

include "include/libdb.php";

dbconnect();
if (updatedb()) {
	dbclose();
	?>
	<body onload="alert('The database was updated successfully');"><?moveto('./admin.php');?>
	<?
} else {
	dbclose();
	?>
	<body onload="alert('The database update failed');"><?moveto('./admin.php');?>
	<?
}
?>
