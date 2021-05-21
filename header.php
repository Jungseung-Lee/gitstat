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
	<title>Git statistics for <?=$projectparams['GIT_TREE_DESC']?></title>
</head>
<body>
	<div id="header">
		<div style="float: left;">
			<div id="logo">
				<h1>
					<a href="./"><font size=6>Git statistics for <?=$projectparams['GIT_PROJECT_NAME']?></font></a>
				</h1>
			</div>
			<div id="hmenu">
				<a href="./index.php">Home</a><a href="./chart.php">Statistics</a><a href="./changelog-find.php">ChangeLog</a><a href="./tag.php">Tags</a><a href="./diff-index.php">DiffManager</a><?
					// Show the admin page in the menu, if we are logged in as an admin
					if (checkprivilege() == 1) {
						echo "<a href=\"./admin.php\">Admin</a>";		
					}
				?>
			</div>
		</div>
		<div style="float: left;">
			<div id="subtitle">
				<!-- FIXME: We should have some reference to the website of the git tree if available -->
				<b><?=$projectparams['GIT_TREE_URL'];?></b>
			</div>
			<div style="float: right;padding 5px 0 0 0;">
				<h1>
					<a href="http://sourceforge.net/projects/gitstat/"<font size=4>(Powered by gitstat <?=$projectparams['GITVER']?>)</font></a>
				</h1>
			</div>
		</div>
	</div>
	<div>
		<div id="articles">
			<div id="left">
	
