<? 
include "include/lib.php";

if (login($_POST['email'],$_POST['password'])) {
	if (checkprivilege() == 1)
		moveto('./admin.php');
	else
		moveto('./index.php');
} else {
	?>
	<body onload="alert('Failed to log in, check your E-mail and password')">
	<?
	moveto('./login.php');
}
?>	
