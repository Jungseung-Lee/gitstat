<?

include "include/lib.php";

$treedescription = getprojectconfig("GIT_TREE_DESC");

?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Git statistics for <?=$treedescription?></title>
	<meta http-equiv='content-Type' content="text/html; charset=UTF-8">
	<link rel='StyleSheet' HREF='./images/style.css' type='text/css' title='style'>
</head>
<body>
<?

dbconnect();

$email = $_GET['email'];

echo "<table width=100% height=100% border=0 cellpadding=0 cellspacing=0><tr><td align=center valign=middle>";

if (!accountexists($email))
	echo "$email is available";
else
	echo "$email cannot be used";

echo "<br><input type=button value='Close' class=input onclick='window.close()'></td></tr></table>";

dbclose();

?>
</body>
</html>
