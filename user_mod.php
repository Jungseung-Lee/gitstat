<?

include "include/lib.php";

$giturl = getprojectconfig("GITURL");

if (!strstr($_SERVER["HTTP_REFERER"], $giturl)) {
        echo "<meta http-equiv='refresh' content='1;url=./index.php'>You're not allowed to access this page directly";
        return;
}

dbconnect();

if($_POST['password']){
	$query="update Members set email='".$_POST['email']
		."', password=password('".$_POST['password'].
		"'), privilege='".$_POST['privilege']."' where no=".$_POST['no'];
}else{
	$query="update Members set email='".$_POST['email']
		."', privilege='".$_POST['privilege']."' where no=".$_POST['no'];
}	
mysql_query($query);

dbclose();

moveto("admin.php?part=5&no=".$_POST['no']);

?>
