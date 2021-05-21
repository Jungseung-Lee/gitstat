<?
set_time_limit(0);
include "header.php";
include "include/libdb.php";

if (loggedin() && checkprivilege() == 1) {

	// We are logged in as an admin user
	$config_ok = false;

	// If it's the loading of the first page we will retrieve the current settings
	if ($_POST['checkconfig'] != true && $_POST['saveconfig'] != true) {

		$projectparams = getprojectconfig();

	} else {

		// Some settings are vital for the correct operation. Others are just some text.

		// Check JpGraph 
		$jpgraph_ok = false;
		$jpgraph_status = "Couldn't not find JpGraph";
		include_once($_POST['jpgraph']."/jpgraph.php");
		if (defined('JPG_VERSION')) {
			// TODO: Check if the version is ok for us
			$jpgraph_ok = true;
			$jpgraph_status = "Version of JpGraph is : ".JPG_VERSION;
		}

		// Check GeSHi 
		$geshi_ok = false;
		$geshi_status = "Couldn't not find GeSHi";
		include_once($_POST['geshi']."/geshi.php");
		if (defined('GESHI_VERSION')) {
			// TODO: Check if the version is ok for us
			$geshi_ok = true;
			$geshi_status = "Version of GeSHi is : ".GESHI_VERSION;
		}

		// Check the diff directory
		$diffdir_ok = false;
		if (file_exists($_POST['rwdir']."/diff")) {
			if (is_writable($_POST['rwdir']."/diff")) {
				$diffdir_ok = true;
				$diffdir_status = "Diff directory exists and can be written to";
			} else {
				$diffdir_status = "Diff directory exists but can't be written to";
			}
		} else {
			$diffdir_status = "Diff directory can't be found";
		}

		// Check the git executable
		$git_ok = false;
		if (file_exists($_POST['git']."/git")) {
			if (is_executable($_POST['git']."/git")) {
				$git_ok = true;
				$handle = popen($_POST['git']."/git --version", "r");
				$version = stream_get_contents($handle);
				pclose($handle);
				$version = preg_replace("/git version /","", $version);
				$git_status = "Version of git : $version";
			} else {
				$git_status = "Found git but it's not executable";
			}
		} else {
			$git_status = "Could not find git";
		}

		// Check the clone of the git tree
		$clone_ok = false;
		if (file_exists($_POST['clone']."/.git")) {
			if (is_readable($_POST['clone']."/.git")) {
				$clone_ok = true;
				$clone_status = "Clone of the git tree exists and can be read (contents not checked)";
			} else {
				$clone_status = "Clone of the git tree exists but can't be read from";
			}
		} else {
			$clone_status = "Clone of the git tree can't be found";
		}

		// TODO: Check if the versions match our minimum requirements

		// Check the URL of this page
		$url_ok = false;
		$url_status = "The url doesn't match this site";
		if ($_SERVER["HTTP_REFERER"] == $_POST['url']."/admin.php?part=8") {
			$url_ok = true;
			$url_status = "The url seems ok";
		}

		// Project related
		$projectparams['GIT_PROJECT_NAME'] = $_POST['shortname'];
		$projectparams['GIT_TREE_DESC'] = $_POST['shortdesc'];
		$projectparams['GIT_TREE_OWNER'] = $_POST['owner'];
		$projectparams['GIT_TREE_URL'] = $_POST['treeurl'];
		$projectparams['GITDIR'] = $_POST['clone'];
		// Tools
		$projectparams['GRAPHMODULEDIR'] = $_POST['jpgraph'];
		$projectparams['GESHIDIR'] = $_POST['geshi'];
		$projectparams['GIT_EXE_PATH'] = $_POST['git'];
		$projectparams['RWDIR'] = $_POST['rwdir'];
		// Gitstat related
		$projectparams['GITURL'] = $_POST['url'];

		// Are all tests ok
		if ($jpgraph_ok && $geshi_ok && $diffdir_ok && $url_ok) {
			$config_ok = true;
			if ($_POST['saveconfig'] == true) {
				// We will save the config to a new file
				// TODO: Move these parameters to the database
				$save_ok = false;
				$save_status = "Gitstat config could not be saved";
				$config = "gstat_rw/config/gitstatconfig.php";
				$handle = fopen($config, "w");
				if ($handle) {
					fwrite($handle, "<?\n");
					fwrite($handle, "// Project related\n");
					fwrite($handle, "\$GSTAT['GIT_PROJECT_NAME'] = \"".$projectparams['GIT_PROJECT_NAME']."\";\n");
					fwrite($handle, "\$GSTAT['GIT_TREE_DESC'] = \"".$projectparams['GIT_TREE_DESC']."\";\n");
					fwrite($handle, "\$GSTAT['GIT_TREE_OWNER'] = \"".$projectparams['GIT_TREE_OWNER']."\";\n");
					fwrite($handle, "\$GSTAT['GIT_TREE_URL'] = \"".$projectparams['GIT_TREE_URL']."\";\n");
					fwrite($handle, "\$GSTAT['GITDIR'] = \"".$projectparams['GITDIR']."\";\n");
					fwrite($handle, "// Tools\n");
					fwrite($handle, "\$GSTAT['GRAPHMODULEDIR'] = \"".$projectparams['GRAPHMODULEDIR']."\";\n");
					fwrite($handle, "\$GSTAT['GESHIDIR'] = \"".$projectparams['GESHIDIR']."\";\n");
					fwrite($handle, "\$GSTAT['GIT_EXE_PATH'] = \"".$projectparams['GIT_EXE_PATH']."\";\n");
					fwrite($handle, "\$GSTAT['RWDIR'] = \"".$projectparams['RWDIR']."\";\n");
					fwrite($handle, "// Gitstat related\n");
					fwrite($handle, "\$GSTAT['GITURL'] = \"".$projectparams['GITURL']."\";\n");
					fwrite($handle, "?>\n");
					fclose($handle);
					$save_ok = true;
					$save_status = "Gitstat config is saved";
				}
				// We will also create a perl config file. This file can be copied to the 
				// correct place. It's a bit of hack of course.
				$perlconfig = "gstat_rw/config/perlconfig.pl";
				$handle = fopen($perlconfig, "w");
				if ($handle) {
					$dbparams = getdbconfig();
					fwrite($handle, "#!/usr/bin/perl\n");
					fwrite($handle, "# Autogenerated by admin.php\n\n");
					fwrite($handle, "our \$host_name_web = '".$projectparams['GITURL']."';\n");
					fwrite($handle, "our \$repository = '".$projectparams['GITDIR']."/.git';\n");
					fwrite($handle, "our \$kfm_rw = '".$projectparams['RWDIR']."';\n\n");
					fwrite($handle, "our \$GIT_PATH = '".$projectparams['GIT_EXE_PATH']."/';\n");
					fwrite($handle, "our \$dsn = 'dbi:mysql:".$dbparams['DB']."';\n");
					fwrite($handle, "our \$user = '".$dbparams['ID']."';\n");
					fwrite($handle, "our \$pass = '".$dbparams['PASS']."';\n\n");
					fwrite($handle, "our \$log = 1;\n");
					fwrite($handle, "our \$admin = 'admin@thishost.com';\n");
					fwrite($handle, "our \$mail_head = '".$projectparams['GIT_PROJECT_NAME']." changes notification e-mail';\n");
					fwrite($handle, "our \$mail_tail = 'Thank you.';\n\n");
					fwrite($handle, "our \$support_rss = 1;\n");
					fwrite($handle, "our \$max_rss = 1000;\n\n");
					fwrite($handle, "return 1;\n");
					fclose($handle);
				}
			}
		}
	}

	?>
	<h2><a href="<?=$_SERVER['PHP_SELF']?>">Administration</a></h2>
	<?
	dbconnect();
	$dbversion_current = checkdbversion();
	$dbversion_needed = checkneededdb();
	if ($dbversion_needed != $dbversion_current) {
		?>
		<p>
		You're database schema is not up-to-date. It should be '<?echo $dbversion_needed;?>' but it is '<?echo $dbversion_current?>'.
		</p>
		<ul>
		<li><a href="admin.php?part=99">Update database schema</a></li>
	<?} else {
		?>
		<ul>
	<?}?>
		<!--	<li><a href="admin.php?part=1">Kernel Source</a></li>-->
		<li><a href="admin.php?part=2">User Info</a></li>
		<!--<li><a href="admin.php?part=3">ChangeLog</a></li>-->
		<li><a href="admin.php?part=4">DiffManager</a></li>
		<!-- <li><a href='./logout.php?url=admin.php'">Logout</a></li>-->
		<li><a href="admin.php?part=6">Manage tags</a> (not yet fully implemented)</li>
		<li><a href="admin.php?part=8">Manage gitstat configuration</a></li>
	</ul>
	<?

	switch($_GET['part']){
		
		case 2: //User Modify
			?>
			<table class=admin_main_member_table border=0 cellpadding=0 cellspacing=0>
			<tr>
				<td colspan=4 class=admin_main_reg_table_title>User List</td>					
			</tr>
			<?
			if(!$_GET['page']) $page=1;
			else $page=$_GET['page'];
			$start=($page-1)*15;
		        $last=15;
			$total=mysql_result(mysql_query("select count(*) from Members"),0,0);
			
			$res=mysql_query("select * from Members order by no desc limit $start,$last");
			while($data=mysql_fetch_array($res)){
			?>
			<tr>
			  <td class=admin_main_reg_table_item_name><a href="./admin.php?part=5&no=<?=$data['no']?>"><?=$data['email']?></a></td>
			  <td class=admin_main_reg_table_item><a href="#" onclick="if(confirm('Really do you want delete him or her?')){ window.location='user_del.php?no=<?=$data[no]?>'; }">Delete</a></td>
			</tr>
			<?
			}
			?>
			<tr>
			  <td colspan=4 class=admin_main_reg_table_bottom>
			  <?
				if($total%15==0) $pages=intval($total/15);
			    else  $pages=intval($total/15)+1;
				if($page<=3){ $pagef=1; $pagel=7; }
				else if($page>=$pages-3){
					$pagef=$pages-7;
					$pagel=$pages; 
				}else {
					$pagef=$page-3;
					$pagel=$page+3;
				}
				if($pages<=7){ 
					$pagef=1;
					$pagel=$pages; 
				}
				$s_flag="&part=3";
			  ?>
				<a href='./admin.php?page=1<?=$s_flag?>'>
					<img src='./images/forworddot.gif' alt='' style='border:0'>
				</a>
			<?   
				if($page>1){
					$page10m=$page-1;
			?>
				<a href='./admin.php?page=<?=$page10m?><?=$s_flag?>'>
					[Previous]
				</a>
			<?
	            }
                   if($page>10){
			        $page10m=$page-10;
			?>
				<a href='./admin.php?page=<?=$page10m?><?=$s_flag?>'>
					[-10]
				</a>
			<?
				}            
           		for($i=$pagef;$i<=$pagel;$i++){
	        ?>
		        <a href='./admin.php?page=<?=$i?><?=$s_flag?>'>
					<?if($page==$i){ echo"<b>".$i."</b>";}else echo"$i";?>
				</a>
		    <?
				}
             		if($pages-$page>10){
			        $page10p=$page+10;
			?>
				<a href='./admin.php?page=<?=$page10p?><?=$s_flag?>'>
					[+10]
				</a>
			<?
				}
			    if($pages-$page>0){
				    $page10p=$page+1;
			?>
				<a href='./admin.php?page=<?=$page10p?><?=$s_flag?>'>
					[Next]
				</a>
			<?
				}
			?>
				<a href='./admin.php?page=<?=$pages?><?=$s_flag?>'>
					<img src='./images/ffdot.gif' alt='' style='border:0'>
				</a>
			  </td>
			</tr>
			</table><?
			break;
		case 4: //DiffManager
			?>
			<script>
			function check(){
				if(!document.diff_admin_del_form.fsize.value && !document.diff_admin_del_form.fcount.value){
					alert("please input file size or diff view count");
					return;
				}else{
					if(document.diff_admin_del_form.fsize.value){
						if(!(/^[0-9]{1,}$/.test(document.diff_admin_del_form.fsize.value))){
							alert("please input file size. integer");
							return;
						}
					}
					if(document.diff_admin_del_form.fcount.value){
						if(!(/^[0-9]{1,}$/.test(document.diff_admin_del_form.fcount.value))){
							alert("please input diff view count, integer");
							return;
						}
					}
					if(confirm('Really do you want delete?')){
						document.diff_admin_del_form.submit();
					}
				}
			}
			</script>
			
			<form action="diff_del_grp.php" method=post name=diff_admin_del_form>
				<table border=0 width=500px cellpadding=0 cellspacing=0>
					<tr>
						<td align=center>if you want to delete diff files & records of table, use below menu.
						</td>
					</tr>
					<tr>
						<td align=center><span>Delete diff file by Size : Under <input type=text size=12 name=fsize>KB&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><br>
						</td>
					</tr>
					<tr>
						<td align=center><span>Delete diff file by Count : Under <input type=text size=12 name=fcount>
							<input type=button value=Delete onclick="check()"></span>
						</td>
					</tr>
				</table>
				
			</form>
			<table border=0 cellpadding=0 cellspacing=0  style="border:1px solid #aaaaaa">
				<tr>
					<td width=200px height=30px style="border-bottom:1px solid #aaaaaa">FileName</td>
					<td width=100px style="border-bottom:1px solid #aaaaaa">FileSize</td>
					<td width=50px style="border-bottom:1px solid #aaaaaa">Count</td>
					<td width=50px style="border-bottom:1px solid #aaaaaa">Delete</td>
				</tr>
			<?
				if(!$_GET['page']) $page=1;
				else $page=$_GET['page'];
				$start=($page-1)*15;
			        $last=15;
				$total=@mysql_result(mysql_query("select count(*) from diffmanager"),0,0);
				$res=mysql_query("select * from diffmanager order by count desc limit $start,$last");
				while($data=@mysql_fetch_array($res)){
				?>
				<tr>
					<td height=20px align=left style='padding-left:5px;'><a href="./gstat_rw/diff/<?=$data['diff_filename']?>"><?=$data['diff_filename']?></td>
					<td style='text-align:right'><?=number_format($data['diff_filesize'])?></td>
					<td><?=$data['count']?></td>
			  		<td><a href="#" onclick="if(confirm('Really do you want delete it?')){ window.location='diff_del.php?no=<?=$data[no]?>'; }">Delete</a></td>
				</tr>
				<?
				}
			?>
			<tr>
			  <td colspan=4 class=admin_main_reg_table_bottom>
			  <?
				if($total%15==0) $pages=intval($total/15);
			    else  $pages=intval($total/15)+1;
				
				if($page<=3)
				{ $pagef=1; $pagel=7; }
				else if($page>=$pages-3){
					$pagef=$pages-7;
					$pagel=$pages; 
				}else {
					$pagef=$page-3;
					$pagel=$page+3;
				}
				
				if($pages<=7){ 
					$pagef=1;
					$pagel=$pages; 
				}
				$s_flag="&part=4";
			  ?>
				<a href='./admin.php?page=1<?=$s_flag?>'>
					<img src='./images/forworddot.gif' alt='' style='border:0'>
				</a>
			<?   
				if($page>1){
					$page10m=$page-1;
			?>
				<a href='./admin.php?page=<?=$page10m?><?=$s_flag?>'>
					[Previous]
				</a>
			<?
	            }
                   if($page>10){
			        $page10m=$page-10;
			?>
				<a href='./admin.php?page=<?=$page10m?><?=$s_flag?>'>
					[-10]
				</a>
			<?
				}            
           		for($i=$pagef;$i<=$pagel;$i++){
	        ?>
		        <a href='./admin.php?page=<?=$i?><?=$s_flag?>'>
					<?if($page==$i){ echo"<b>".$i."</b>";}else echo"$i";?>
				</a>
		    <?
				}
             		if($pages-$page>10){
			        $page10p=$page+10;
			?>
				<a href='./admin.php?page=<?=$page10p?><?=$s_flag?>'>
					[+10]
				</a>
			<?
				}
			    if($pages-$page>0){
				    $page10p=$page+1;
			?>
				<a href='./admin.php?page=<?=$page10p?><?=$s_flag?>'>
					[Next]
				</a>
			<?
				}
			?>
				<a href='./admin.php?page=<?=$pages?><?=$s_flag?>'>
					<img src='./images/ffdot.gif' alt='' style='border:0'>
				</a>
			  </td>
			</tr>
			</table>
			<?
			break;
		
		case 5: //User Modify
			$result=mysql_query("select * from Members where no='".$_GET['no']."'");
			while($data=mysql_fetch_array($result)){
			?>
			<div>
			<form action="user_mod.php" method=post>
			<table width=200 align=left>
			<tr>
			  <td align=left>E-mail : </td>
			  <td align=left><input type=text name=email size=12 value=<?=$data['email']?> ></td>
			</tr>
			<tr>
			  <td align=left>Password : </td>
			  <td align=left><input type=password name=password size=12></td>
			</tr>
			<tr>
			  <td align=left>Privilege : </td>
			  <td align=left>
			  	<select name=privilege>
				<?for($i=1;$i<=5;$i++){?>
					<option value=<?=$i?> <? if($data['privilege']==$i){ echo"selected=selected"; }?>>
						<?=$i?>
						<?
						switch($i){
							case 1: echo" : Admin";
								break;
							case 2:
							case 3:
							case 4: echo" : None ";
								break;
							case 5: echo" : user ";
								break;
						}
						?>
					</option>
				<?}?>
				</select>
			  </td>
			</tr>
			</table>
			<input type=submit value='Confirm'>
			<input type=hidden value=<?=$_GET['no']?> name=no>
			</form>
			</div><?
			}
			break;

		case 6: //Manage tags

			$query = "SELECT * FROM v_tag ORDER BY epoch DESC";
			$result = mysql_query($query);

			?>
			<h2><a href="<?=$_SERVER['PHP_SELF']?>?part=6">Tags</a></h2>
			<p>
			Selecting 'Show' for a tag means that it will be used when generating charts that are tag specific. This can be
			useful for example when you don't want to see the release candidates for the kernel.
			</p>
			<p class="date"><img src="images/timeicon.gif" alt="" /></p>

			<form name=tagmgmt method=post action='./modify_tags.php' enctype='multipart/form-data'>
				<table border=0 cellpadding=0 cellspacing=0>
					<th width=50px align="center">Show</th>
					<th width=100px align="left">Tag</th>
				<?

				while($data = mysql_fetch_array($result)){
					?>
					<tr>
						<td align="center">
							<input type=checkbox name=tag<?=$data['no']?> value=true<?if($data['visible'] == 1){echo " checked";}?>>
						</td>
						<td algin="left">
							<b><?=$data['name']?></b>
						</td>
					</tr>
					<?
				}

				// Check if the db can handle these changes
				if ($dbversion_current >= 3) {
					?>
					<tr>
						<td colspan=2 align=right><input type=submit value="Submit changes"></td>
					</tr>
					<?
				}?>
				</table>
			</form>
			<?
			break;

		case 8: //Manage gitstat configuration

			?>
			<h2><a href="<?=$_SERVER['PHP_SELF']?>?part=8">Configuration</a></h2>
			<p>
			These settings are needed to make sure gitstat functions well
			</p>
			<p class="date"><img src="images/timeicon.gif" alt="" /></p>

			<form name=dbsettings method=post action='<?=$_SERVER['PHP_SELF']?>?part=8' enctype='multipart/form-data'>

				<table border=0 cellpadding=0 cellspacing=0>
					<tr>
						<td width=200px align=left></td>
						<td align=left></td>
					</tr>
					<tr><td><h2>Project related stuff</h2><br></td></tr>
					<tr>
						<td>Short name for the project</td>
						<td><input type=text size=40 name=shortname tabindex=1 value="<?=$projectparams['GIT_PROJECT_NAME']?>"></td>
					</tr>
					<tr>
						<td>Short description of the project</td>
						<td><input type=text size=40 name=shortdesc tabindex=2 value="<?=$projectparams['GIT_TREE_DESC']?>"></td>
					</tr>
					<tr>
						<td>Owner of the project</td>
						<td><input type=text size=40 name=owner tabindex=3 value="<?=$projectparams['GIT_TREE_OWNER']?>"></td>
					</tr>
					<tr>
						<td>Where is the offical Git tree</td>
						<td><input type=text size=40 name=treeurl tabindex=4 value="<?=$projectparams['GIT_TREE_URL']?>"></td>
					</tr>
					<tr>
						<td>Where is the local clone of the Git tree</td>
						<td><input type=text size=40 name=clone tabindex=9 value="<?=$projectparams['GITDIR']?>"></td>
					</tr>
					<tr>
						<td width=180px colspan=2 align=right>
							<font color=<?echo $clone_ok?"blue":"red"?>><b><?=$clone_status?></b></font>
						</td>
					</tr>
					<tr><td><h2>Tools</h2><br></td></tr>
					<tr>
						<td>Where can we find <a href="http://www.aditus.nu/jpgraph/">JpGraph</a></td>
						<td><input type=text size=40 name=jpgraph tabindex=5 value="<?=$projectparams['GRAPHMODULEDIR']?>"></td>
					</tr>
					<tr>
						<td width=180px colspan=2 align=right>
							<font color=<?echo $jpgraph_ok?"blue":"red"?>><b><?=$jpgraph_status?></b></font>
						</td>
					</tr>
					<tr>
						<td>Where can we find <a href="http://qbnz.com/highlighter/">GeSHi</a></td>
						<td><input type=text size=40 name=geshi tabindex=6 value="<?=$projectparams['GESHIDIR']?>"></td>
					</tr>
					<tr>
						<td width=180px colspan=2 align=right>
							<font color=<?echo $geshi_ok?"blue":"red"?>><b><?=$geshi_status?></b></font>
						</td>
					</tr>
					<tr>
						<td>Where can we find <a href="http://git.or.cz/">Git</a></td>
						<td><input type=text size=40 name=git tabindex=10 value="<?=$projectparams['GIT_EXE_PATH']?>"></td>
					</tr>
					<tr>
						<td width=180px colspan=2 align=right>
							<font color=<?echo $git_ok?"blue":"red"?>><b><?=$git_status?></b></font>
						</td>
					</tr>
					<tr>
						<td>Where is the read write directory</td>
						<td><input type=text size=40 name=rwdir tabindex=7 value="<?=$projectparams['RWDIR']?>"></td>
					</tr>
					<tr>
						<td width=180px colspan=2 align=right>
							<font color=<?echo $diffdir_ok?"blue":"red"?>><b><?=$diffdir_status?></b></font>
						</td>
					</tr>
					<tr><td><h2>Gitstat related</h2><br></td></tr>
					<tr>
						<td>What's the URL to get to this site</td>
						<td><input type=text size=40 name=url tabindex=8 value="<?=$projectparams['GITURL']?>"></td>
					</tr>
					<tr>
						<td width=180px colspan=2 align=right>
							<font color=<?echo $url_ok?"blue":"red"?>><b><?=$url_status?></b></font>
						</td>
					</tr>
					<? if ($config_ok) {
					?>
					<tr>
						<td colspan=2 align=right><br><input type=submit value="Save configuration"></td>
						<input type=hidden name=saveconfig value=true>
					</tr>
					<?} else {
					?>
					<tr>
						<td colspan=2 align=right><br><input type=submit value="Check configuration"></td>
						<input type=hidden name=checkconfig value=true>
					</tr>
					<?}?>
					<tr>
						<td width=180px colspan=2 align=right>
							<font color=<?echo $save_ok?"blue":"red"?>><b><?=$save_status?></b></font>
						</td>
					</tr>
				</table>
			</form>
			<?
			break;

		case 99: //Database update

			?>
			<h2><a href="<?=$_SERVER['PHP_SELF']?>?part=99">Database update</a></h2>
			<p>
			The database schema needs to be updated to the latest version to make use of all the features. Clicking the button
			will make sure that all needed updates are performed.
			</p><br>
			<form name=dbupdate method=post action='updatedb.php' enctype='multipart/form-data'>
				<table border=0 cellpadding=0 cellspacing=0>
					<tr>
						<td>
							<input type=submit value="Update database">
						</td>
					</tr>
				</table>
			</form>
			<?

			break;

		default:
			?>
			<?
			break;
	}
} elseif (loggedin() && checkprivilege() != 1) {
	// Logged in as a normal user
	?>
	<meta http-equiv='refresh' content='5;url=./index.php'>
	You have to be logged in as an admin user to use this page. You will be redirected to the homepage.
	<?
} else {
	// Not logged in at all
	?>
	<meta http-equiv='refresh' content='5;url=./login.php'>
	You have to login as an admin user to use this page. You will be redirected to the login page.
	<?
}

include "footer.php";
?>
