<?
include "include/lib.php";

if (loggedin()) {
	dbconnect();

	$email = $_POST['email'];
	$password = $_POST['password'];

	if ($_POST['emailhtml'] == true)
		$emailhtml = 1;
	else
		$emailhtml = 0;
	
	$privilege = 5;

	if ($password)
		$s_pass = ", `password` = PASSWORD('$password')";
	else
		$s_pass = "";

	$time=time();

	if (!mysql_query("UPDATE `Members` SET `email` = '$email' $s_pass, `mailhtml` = '$emailhtml' WHERE `email` = '$email'")) {
		err(mysql_error(),"join.php");
	} else {
		$userno = mysql_result(mysql_query("SELECT `no` FROM `Members` WHERE `email` = '$email'"),0,0);

		mysql_query("DELETE FROM `Memcategory` WHERE `user_no` = '$userno'");

		for ($i = 0;$i < 20;$i++) {
			if ($_POST['cate_list_'.$i]) {
				mysql_query("INSERT INTO `Memcategory` (`user_no`, `subcategory1`) VALUES('$userno','".$_POST['cate_list_'.$i]."')");
			}
		}
				
		for ($i = 20;$i < 100;$i++) {
			if ($_POST['cate_list_'.$i]) {
				mysql_query("INSERT INTO `Memcategory` (`user_no`,`filename`) VALUES('$userno','".$_POST['cate_list_'.$i]."')");
			}
		}
		?>
		<body onload="alert('Your modifications were changed successfully');"><?moveto("./index.php");?>
		<?
	}
	dbclose();
} else {
	err("Need Login.","");
}
?>
