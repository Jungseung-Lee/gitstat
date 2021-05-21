<?
include "include/lib.php";

if (!($user_email = getuser()))
	$user_email = "gitstat";

$headers = "From: ".$user_email."\r\n";
//specify MIME version 1.0
$headers .= "MIME-Version: 1.0\r\n";

//unique boundary
$boundary = uniqid("HTMLDEMO");

//tell e-mail client this e-mail contains//alternate versions
$headers .= "Content-Type: multipart/mixed; boundary = $boundary\r\n\r\n";

//plain text version of message
$body = "--$boundary\r\n" .
"Content-Type: text/plain; charset=UTF-8\r\n" .
"Content-Transfer-Encoding: base64\r\n\r\n";
$body .= chunk_split(base64_encode($_POST['memo']));

//send message
mail($_POST['email'], $_POST['subject'], $body, $headers);
?>
<body onload="window.alert('Mail was sent');">
<?
	if($_POST['admin'])
		moveto("admin.php?part=6");
	else
		moveto("commit-detail.php?commit=".$_POST['commit']);
?>
