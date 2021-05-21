<?

include "include/lib.php";

dbconnect();

$email=$_POST['email'];
$password=$_POST['password'];
if($_POST['emailhtml'] == true)
{
	$emailhtml = 1;
}
else
{
	$emailhtml = 0;
}
$privilege=5;

if($email == '' || $password == '')
{
	?>
	<body onload="alert('Please, enter email address or password');"><?moveto("./join.php");?>
	<? 
}

// Check if this email is already registered
if (accountexists($email))
	$joined = 1;
else
	$joined = 0;

if(!$joined){

	$time=time();

	if(!mysql_query("insert into Members (email,password,privilege,mailhtml)
			values('$email',password('$password'),'$privilege','$emailhtml')"))
	{ 
		err(mysql_error(),"join.php");
	}
	else{
		for($i=0;$i<20;$i++){
			if($_POST['cate_list_'.$i]){
				$userno=mysql_result(mysql_query("select no from Members where email='$email'"),0,0);
				mysql_query("insert into Memcategory (user_no,subcategory1) values('$userno','".$_POST['cate_list_'.$i]."')");
			}
		}

		for($i=20;$i<100;$i++){
			if($_POST['cate_list_'.$i]){
				$userno=mysql_result(mysql_query("select no from Members where email='$email'"),0,0);
				mysql_query("insert into Memcategory (user_no,filename) values('$userno','".$_POST['cate_list_'.$i]."')");
			}
		}
		?>
		<body onload="alert('Your E-mail was registered successfully!\n<?echo "ID: ".$email;?>');"><?moveto("./index.php");?>
		<?
	}

}else{
	?><body onload="alert('You are already registered!');"><?moveto("./join.php");?>
	<?
}
?>
