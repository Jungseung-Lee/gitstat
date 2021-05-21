<?

include "include/lib.php";

$giturl = getprojectconfig("GITURL");

if (!strstr($_SERVER["HTTP_REFERER"], $giturl)) {
        echo "<meta http-equiv='refresh' content='1;url=./index.php'>You're not allowed to access this page directly";
        return;
}

dbconnect();

$query = "DELETE FROM `Members` WHERE `no` = ".$_GET['no'];
mysql_query($query);

dbclose();

moveto("admin.php?part=2");

?>
