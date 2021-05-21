<? 

include "include/lib.php";

// Get some project specifics
$projectparams = getprojectconfig();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="Keywords" content="linux, Kernel, Statistics, Git" />
	<link rel="stylesheet" type="text/css" href="images/style.css" />
	<title>gitstat</title>
	<script language=javascript>
	
	function checkall(theform) {
		var obj = document.getElementsByName('path[]'); 
		for(var i=0; i < obj.length; i++){

			if(obj[i].checked == true){
				obj[i].checked = false
			}else{
				obj[i].checked = true;
			}
		}
	}
	function confirm(theform) {
		
		var chk_count = 0;
		
		var obj = document.getElementsByName('path[]'); 
		for(var i=0; i < obj.length; i++){
			if(obj[i].checked == true)
			{
				window.opener.add_category_file('Individual  directory or file',obj[i].value,obj[i].value);
				chk_count++;
			}
		}
		
		if(!chk_count)
		{
			alert('Not Selected');
		} 
		else {
			window.close();
		} 
			
	}
	</script>
</head>
<br>
<?

	$dest_str = str_replace("..","",$_GET['path']);

	$command = "ls ".$projectparams['GITDIR']."/".$dest_str." --sort=extension";
	//echo $command."<Br>";
	$handle = popen($command,"r");

?>
	<form name='selectedfile' action="<?=$_SERVER['PHP_SELF']?>" method=GET>
<?
	if($_GET['path'] != NULL){
		$str = $str."<div id=filealign>";
		$str = $str."&nbsp;&nbsp;<a href='javascript:history.go(-1);'>..</a></div>";
	}
	$idx = 0;
	while(!feof($handle)) {
		$buffer = fgets($handle);
		$buffer = rtrim($buffer);
		if($buffer ==NULL) break;

		if(is_dir($projectparams['GITDIR']."/".$_GET['path']."/".$buffer)){

			$path=$_GET['path']."/".$buffer;

			$str = $str."<div id=filealign>";
			$str = $str."<input type=checkbox name=path[] value='".$path."'>";
			$str = $str."<a href='".$_SERVER['PHP_SELF']."?path=".$path."'>".$buffer."</a></div>";
		}
		else
		{
			$path=$_GET['path']."/".$buffer;
			$str = $str."<div id=filealign>";
			$str = $str."<input type=checkbox name=path[] value='".$path."'>";
			$str = $str.$buffer."</div>";
		}
		$idx ++;
		ob_flush();
		flush();
	}

	if($str != "") echo $str;//.=$buffer;

?>
<br>
<div width=500px height=200px align=center>
<input type=button class=input value='check all' onclick="checkall(this)">
<input type=button class=input value='confirm' onclick="confirm(this)"><br>
</div></form>
