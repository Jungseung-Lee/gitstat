<?
include "header.php";
include "include/libdb.php";
include "include/libgather.php";

dbconnect();
// Update the database if needed
updatedb();

$dbversion_current = checkdbversion();

// We only want a maximum of $showcount releases per page, set the default.
// FIXME: If labels are too big they can overlap (should $showcount be influenced by this ?)
$default_showcount = 10;

?>

<script>

var data = new Array();

function view_option(arg){

	if(arg==1 
		|| arg==2 
		|| arg==8
		|| arg==9
		|| arg==10
		|| arg==11)
		{
		var t1=document.getElementById("option1");
		t1.style.display="none";
		var t2=document.getElementById("option2");
		t2.style.display="block";
		var t3=document.getElementById("option3");
		t3.style.display="none";	
	}else if( arg==3  
		|| arg==5 
		|| arg==7)
		{
		var t1=document.getElementById("option1");
		t1.style.display="block";
		var t2=document.getElementById("option2");
		t2.style.display="none";
		var t3=document.getElementById("option3");
		t3.style.display="none";
	}else if(arg==4 
		|| arg==6
		|| arg==12)
		{
		var t1=document.getElementById("option1");
		t1.style.display="none";
		var t2=document.getElementById("option2");
		t2.style.display="none";
		var t3=document.getElementById("option3");
		t3.style.display="block";	
	}else
		{
		var t1=document.getElementById("option1");
		t1.style.display="none";
		var t2=document.getElementById("option2");
		t2.style.display="block";
		var t3=document.getElementById("option3");
		t3.style.display="none";
	}
}
		
function view_month1(){
	var today=new Date();
	var year1=document.getElementById('chart_parameter2_year');
	var month1=document.getElementById('chart_parameter2_month');
	var i,j;	
	//alert(year1.value+" "+(today.getYear()+1900));
	for(j=0;j<12;j++)
	{
		if(data[j])
			month1.removeChild(data[j]);
	}

	data = new Array();

	if(year1.value==1900+today.getYear() || year1.value==today.getYear()){
		for(i=0;i<(today.getMonth()+1);i++){
			data[i]=document.createElement("option");
			data[i].setAttribute("value",i+1);
			data[i].innerHTML=i+1;
			month1.appendChild(data[i]);
		}
	}else{
		for(i=0;i<12;i++){
			data[i]=document.createElement("option");
			data[i].setAttribute("value",i+1);
			data[i].innerHTML=i+1;
			month1.appendChild(data[i]);
		}
	}

	//alert("<?=$_GET['chart_parameter2_month']?>");
	var month1=document.getElementById('chart_parameter2_month');

	<?
		if($_GET['chart_parameter2_month']){
			?>
			<!--	month1.selectedIndex=<?=$_GET['chart_parameter2_month']?>;-->
			<?
		}
	?>
}

</script>

<?

$projectname = getprojectconfig("GIT_PROJECT_NAME");

// FIXME and TODO: We should make adding charts far more easy.
$chart_subject1="Number of changesets (Day)"; 	                 
$chart_subject2="Number of changesets (Month)"; 	                 
$chart_subject3="Top contributor's domain (Date)"; 	                
$chart_subject4="Top contributor's domain ($projectname Release)"; 	                 
$chart_subject5="Top contributors (Date)"; 	                 
$chart_subject6="Top contributors ($projectname Release)"; 	                
$chart_subject7="Per-directory changesets count (Date)"; 	                 
$chart_subject12="Per-directory changesets count ($projectname Release)"; 	                 
$chart_subject8="$projectname release cycle duration"; 	                 
$chart_subject9="Total number of commits for every $projectname release"; 	                 
$chart_subject10="Commits per hour for every $projectname release"; 	                 
$chart_subject11="Total number of $projectname developers";

?>

<!--Subject-->
<h2><a href="<?=$_SERVER['PHP_SELF']?>">Statistics</a></h2>
<div id="statoptionbox">
	<form action="<?=$_SERVER['PHP_SELF']?>" method=GET>
		<div><h3>Graph</h3></div>
		<div id="leftchartmenu" >
			<input type=radio onclick="view_option(1)" name=chart_parameter1 value=1
				<?if($_GET['chart_parameter1']==1 || $_GET['chart_parameter1']==NULL) echo "checked";?>><?=$chart_subject1?><br>
			<input type=radio onclick="view_option(2)" name=chart_parameter1 value=2
				<?if($_GET['chart_parameter1']==2) echo "checked";?>><?=$chart_subject2?><br>
			<input type=radio onclick="view_option(3)" name=chart_parameter1 value=3
				<?if($_GET['chart_parameter1']==3) echo	"checked";?>><?=$chart_subject3?><br>
			<input type=radio onclick="view_option(4)" name=chart_parameter1 value=4
				<?if($_GET['chart_parameter1']==4) echo	"checked";?>><?=$chart_subject4?><br>
			<input type=radio onclick="view_option(5)" name=chart_parameter1 value=5
				<?if($_GET['chart_parameter1']==5) echo	"checked";?>><?=$chart_subject5?><br>
			<input type=radio onclick="view_option(6)" name=chart_parameter1 value=6
				<?if($_GET['chart_parameter1']==6) echo "checked";?>><?=$chart_subject6?><br>
		</div>
		<div id="rightchartmenu">
			<input type=radio onclick="view_option(7)" name=chart_parameter1 value=7
				<?if($_GET['chart_parameter1']==7) echo "checked";?>><?=$chart_subject7?><br>
			<input type=radio onclick="view_option(12)" name=chart_parameter1 value=12
				<?if($_GET['chart_parameter1']==12) echo "checked";?>><?=$chart_subject12?><br>
			<input type=radio onclick="view_option(8)" name=chart_parameter1 value=8
				<?if($_GET['chart_parameter1']==8) echo "checked";?>><?=$chart_subject8?><br>
			<input type=radio onclick="view_option(9)" name=chart_parameter1 value=9
				<?if($_GET['chart_parameter1']==9) echo "checked";?>><?=$chart_subject9?><br>
			<input type=radio onclick="view_option(10)" name=chart_parameter1 value=10
				<?if($_GET['chart_parameter1']==10) echo "checked";?>><?=$chart_subject10?><br>
			<input type=radio onclick="view_option(11)" name=chart_parameter1 value=11
				<?if($_GET['chart_parameter1']==11) echo "checked";?>><?=$chart_subject11?><br>
			<br>
		</div>

		<div id="option3">
			<?
			// Check if we have the visible flag in our schema
			if ($dbversion_current >= 3) {
				$query = "SELECT `name`, `epoch` FROM `v_tag`
					WHERE ((`epoch` IS NOT NULL) AND (`visible` = TRUE)) ORDER BY `epoch` DESC";
			} else {
				// Old style (should be removed as length check is bogus)
				$query="SELECT `name`, `epoch` FROM `v_tag`
					WHERE ((`epoch` IS NOT NULL ) AND (LENGTH(`name`) < 8)) ORDER BY `epoch` DESC";
			}
			$result=mysql_query($query);
			?>
			<select name=chart_parameter_ver id="chart_parameter_ver">
			<?
			while($data=@mysql_fetch_array($result)){
				?><option value=<? echo $data['name'];
				//if($_GET['chart_parameter_ver']==$data['name']){ echo " selected=selected";}
				echo ">";
				echo $data['name'];
				?></option>
				<?
			}
			?>
			</select>
			<input type=submit value="Create graph">
			<input type=hidden name=submit value=1>
		</div>
		<div id="option1">
			<?
			global $recent_year,$oldest_year;
			$recent_date=mysql_result(
					mysql_query(
						"select max(commitdate) from ChangeLog"
						),0,0);

			$oldest_date=mysql_result(
					mysql_query(
						"select min(commitdate) from ChangeLog"
						),0,0);
			$recent_year=date("Y",$recent_date);
			$oldest_year=date("Y",$oldest_date);
			$this_month=date("m",time());
			$this_day=date("d",$recent_date);
			?>
			<select name=chart_parameter2_year id="chart_parameter2_year" onchange="view_month1()">
				<?
				for($i=$recent_year;$i>=$oldest_year;$i--){
					?>
					<option value=<?=$i?>
					<? 
					if($_GET['chart_parameter2_year']==$i
							&& $_GET['submit']==1){ echo "selected=selected"; }
					?>
					><?=$i?></option>
					<?
				}
				?>
			</select>
			<select name=chart_parameter2_month id="chart_parameter2_month">
				<option value=0 >Select Month </option>
			</select>
			<input type=submit value="Create graph">
			<input type=hidden name=submit value=1>
		</div>
		<div id="option2">
			<div id="submit_button" style="float: left;width: 260px;">
				<input type=submit value='Create graph'>
			</div>
			<div id="submit_showcount" style="float: left;width: 340px;">
				Show&nbsp;
				<input maxlength=2 size=2 type=text name=showcount value=<?if($_GET['showcount']) echo $_GET['showcount']; else echo $default_showcount;?>>
				&nbsp;items per graph
			</div>
		</div>

	</form>
</div>

<div id="chartgraph">
	<?
	// Make variablenames a bit easier to use (and shorter)
	$chartnumber = $_GET['chart_parameter1'];
	$showcount   = $_GET['showcount'];
	$page        = $_GET['page'];

	////////////////////////////////////////// Top Page
	if ($chartnumber == ''){
		?>
		<?
	}
	////////////////////////////////////////// recent changeset - Day(1st)
	else if ($chartnumber == 1) {

		if ($_GET['submit']) {

			// FIXME: $showcount can't be bigger then a month as that would mixup the graph as 
			//        we end up with same keys in the array. Limit it to 25

			if ($showcount > 25)
				$showcount = 25;

			// Retrieve the commits
			$commitarray = commits_per_day($showcount, $page);

			while (list($day, $commits) = each($commitarray)) {
				$dd.=date("d",$day).",".$commits.":";
			}

			// FIXME: Should also be moved to an include file.
			//
			// Get the maximum number of commits per day for the entire project
			$query_max = "select max(maxcommits) from (select count(DATE_FORMAT(FROM_UNIXTIME(commitdate),'%Y%m%d')) as maxcommits ";
			$query_max = $query_max."from ChangeLog group by DATE_FORMAT(FROM_UNIXTIME(commitdate),'%Y%m%d')) as t1";
			$result_max=@mysql_query($query_max);
			$data_max=@mysql_fetch_row($result_max);
			$max_commits = $data_max[0];

			?>
			<h1 align=center><?=$chart_subject1?></h1>
			<center>
				<table border=0 cellpadding=0 cellspacing=0>
					<tr>
						<td valign=bottom><a href="<?=$_SERVER['PHP_SELF']?>?
							chart_parameter1=<?=$chartnumber?>&
							showcount=<?=$showcount?>&
							submit=1&
							page=<? echo $page+1;?>">
							<font size=5><</font>
							</a><br>
						</td>
						<td><a href="./bargraph.php?
								subject=<?=urlencode($chart_subject1)?>&
								setscale=<?echo "textlin,0,".$max_commits.",0,0,round"?>&
								data=<?=$dd?>&
								barcolor=red">
								<img class="graph" src="./bargraph.php?
									subject=<?=urlencode($chart_subject1)?>&
									setscale=<?echo "textlin,0,".$max_commits.",0,0,round"?>&
									data=<?=$dd?>&
									barcolor=red" border=0 align=top>
							</a>
						</td>
						<td valign=bottom>
							<? if($page >0 ){
								?>
								<a href="<?=$_SERVER['PHP_SELF']?>?
									chart_parameter1=<?=$chartnumber?>&
									showcount=<?=$showcount?>&
									submit=1&
									page=<? echo $page-1;?>">
									<font size=5>></font>
								</a>
							<?}?>
						</td>
					</tr>
				</table>
			</center>
			<?		
		}
	}	
	////////////////////////////////////////// recent changeset per Month(2nd)
	else if ($chartnumber == 2) {

		if($_GET['submit']){

			// Retrieve the commits
			$commitarray = commits_per_month($showcount, $page);

			while (list($month, $commits) = each($commitarray)) {
				$dd.=date('M/y', $month).",".$commits.":";
			}

			?>
			<h1 align=center><?=$chart_subject2?></h1>
			<center>
				<table border=0 cellpadding=0 cellspacing=0>
					<tr>
						<?if (TRUE /* FIXME: Don't show the left arrow if not needed */) {
							?>
							<td valign=bottom><a href="<?=$_SERVER['PHP_SELF']?>?
								chart_parameter1=<?=$chartnumber?>&
								showcount=<?=$showcount?>&
								submit=1&
								page=<?echo $page+1;?>"
								title="Show previous releases">
								<font size=5><</font>
								</a><br>
							</td>
							<?
						}?>
						<td><a href="./bargraph.php?
								subject=<?=urlencode($chart_subject2)?>&
								data=<?=$dd?>&
								barcolor=red">
								<img class="graph" src="./bargraph.php?
									subject=<?=urlencode($chart_subject2)?>&
									data=<?=$dd?>&
									barcolor=red" border=0 align=top>
							</a>
						</td>
						<?if ($page > 0) {
							?>
							<td valign=bottom><a href="<?=$_SERVER['PHP_SELF']?>?
								chart_parameter1=<?=$chartnumber?>&
								showcount=<?=$showcount?>&
								submit=1&
								page=<?echo $page-1;?>"
								title="Show next releases">
								<font size=5>></font>
								</a><br>
							</td>
							<?
						}?>
					</tr>
				</table>
			</center>
			<?	
		}
	}
	////////////////////////////////////////// Top Author Domain (3rd Page)
	else if ($chartnumber == 3) {

		if($_GET['submit']){

			if($_GET['chart_parameter2_year'] && !$_GET['chart_parameter2_month']){

				$startdate = gmmktime(0, 0, 0, 1, 1, $_GET['chart_parameter2_year']);
				$enddate = gmmktime(0, 0, 0, 1, 1, $_GET['chart_parameter2_year'] + 1);
				$chart_subject = preg_replace("/\(Date\)/", "(".$_GET['chart_parameter2_year'].")", $chart_subject3);

			}else if($_GET['chart_parameter2_year'] && $_GET['chart_parameter2_month']){

				$startdate = gmmktime(0, 0, 0, $_GET['chart_parameter2_month'], 1, $_GET['chart_parameter2_year']);
				$enddate = gmmktime(0, 0, 0, $_GET['chart_parameter2_month'] + 1, 1, $_GET['chart_parameter2_year']);
				$chart_subject = preg_replace("/\(Date\)/","(".gmdate("F", $enddate)." ".$_GET['chart_parameter2_year'].")", $chart_subject3);

			}else{
				// Should never happen
			}

			// Retrieve the domains for the mentioned dates
			$domainarray = domains_by_date($startdate, $enddate);

			// Number of entries in the array
			$domaincount = count($domainarray);

			if ($domaincount > 0) {

				// Only pass the first 10 to horizbar.php (it will be limited there anyway)
				$showcount = 10;

				$count = 0;
				$commitcount = 0;
				while (list($domain, $commits) = each($domainarray)) {
					if ($count++ < $showcount)
						$dd .= $domain.",".$commits.":";

					$commitcount += $commits;
				}
				reset($domainarray);

				?>
				<h1 align=center><?=$chart_subject?></h1>
				<center>
					<a href="horizbar.php?
							subject=<?=urlencode($chart_subject)?>&
							data=<?=$dd?>">
						<img class="graph" src="horizbar.php?
							subject=<?=urlencode($chart_subject)?>&
							data=<?=$dd?>" border=0 align=top>
					</a>
				</center>

				<table border=0 width=100% height=100% cellpadding=0 cellspacing=0>
					<tr>
						<td valign=top>
							<div>
								<table cellpadding=0 cellspacing=0 class="chart_view_legend_table">
									<tr>
										<td class="chart_view_legend_table_subject1">Domain (Total <?=$domaincount?>)</td>
										<td class="chart_view_legend_table_subject2">Commits (Total <?=$commitcount?>)</td>
									</tr>		
									<?
									while (list($domain, $commits) = each($domainarray)) {
										?>
										<tr>
											<td class="chart_view_legend_table_content1">
												<?$index++; echo $index." "; $domain = substr($domain, 0, 24); echo $domain;?>
											</td>
											<td class="chart_view_legend_table_content2"><?=$commits?></td>
										</tr>
										<?
									}
									?>
								</table>
							</div>
						</td>
					</tr>
				</table>
				<?
			}else{
				?>
				<div class="chart_view_chart">
					No contents for display.<br>
				</div>
			<?}
		}
	}	
	////////////////////////////////////////// Top author domain for a project release (4th page)
	else if ($chartnumber == 4) {

		if ($_GET['submit']) {

			$chart_subject = preg_replace("/Release\)/", "release ".$_GET['chart_parameter_ver'].")", $chart_subject4);

			// Retrieve the domains for the wanted release
			$domainarray = domains_by_release($_GET['chart_parameter_ver']);

			// Number of entries in the array
			$domaincount = count($domainarray);

			// Number of domains we want to show in the graph
			// FIXME: We can't rely on limit passed to piegraph as sometimes the number of domains
			//        is to much to handle (see BUG 1807418).
			//        This also means we don't have a 'etc' in the graph anymore.
			$showcount = 20;

			if ($domaincount > 0) {

				$count = 0;
				$commitcount = 0;
				while (list($domain, $commits) = each($domainarray)) {
					if ($count++ < $showcount)
						$dd .= $domain.",".$commits.":";

					$commitcount += $commits;
				}
				reset($domainarray);
				
				?>
				<h1 align=center><?=$chart_subject?></h1>
				<center>
					<a href="piegraph.php?
						subject=<?=urlencode($chart_subject)?>&
						data=<?=$dd?>&
						order=true">
						<img class="graph" src="piegraph.php?
							subject=<?=urlencode($chart_subject)?>&
							data=<?=$dd?>&
							order=true" border=0 align=top>
					</a>
				</center>

				<table border=0 width=100% height=100% cellpadding=0 cellspacing=0>
					<tr>
						<td valign=top>
							<div>
								<table cellpadding=0 cellspacing=0 class="chart_view_legend_table">
									<tr>
										<td class="chart_view_legend_table_subject1">Domain (Total <?=$domaincount?>)</td>
										<td class="chart_view_legend_table_subject2">Commits (Total <?=$commitcount?>)</td>
									</tr>		
									<?
									while (list($domain, $commits) = each($domainarray)) {
										?>
										<tr>
											<td class="chart_view_legend_table_content1">
												<?$index++; echo $index." "; $domain = substr($domain, 0, 24); echo $domain;?>
											</td>
											<td class="chart_view_legend_table_content2"><?=$commits?></td>
										</tr>
										<?
									}
									?>
								</table>
							</div>
						</td>
					</tr>
				</table>
				<?
			} else {
				?>
				<div class="chart_view_chart">
					No contents for display.<br>
				</div>
				<?
			}
		}
	}
	////////////////////////////////////////// Top Author per Date(5th Page)
	else if ($chartnumber == 5) {

		if ($_GET['submit']) {

			if ($_GET['chart_parameter2_year'] && !$_GET['chart_parameter2_month']) {
				$startdate = gmmktime(0, 0, 0, 1, 1, $_GET['chart_parameter2_year']);
				$enddate = gmmktime(0, 0, 0, 1, 1, $_GET['chart_parameter2_year'] + 1);
				$chart_subject = preg_replace("/\(Date\)/", "(".$_GET['chart_parameter2_year'].")", $chart_subject5);

			}else if ($_GET['chart_parameter2_year'] && $_GET['chart_parameter2_month']) {
				$startdate = gmmktime(0, 0, 0, $_GET['chart_parameter2_month'], 1, $_GET['chart_parameter2_year']);
				$enddate = gmmktime(0, 0, 0, $_GET['chart_parameter2_month'] + 1, 1, $_GET['chart_parameter2_year']);
				$chart_subject = preg_replace("/\(Date\)/","(".gmdate("F", $enddate)." ".$_GET['chart_parameter2_year'].")", $chart_subject5);
			}

			// Retrieve the authors for the wanted release
			$authorarray = authors_by_date($startdate, $enddate);

			// Number of entries in the array
			$authorcount = count($authorarray);

			if ($authorcount > 0) {

				// Number of authors we want to show in the graph
				$showcount = 10;

				$count = 0;
				$commitcount = 0;
				while (list($author,$commits) = each($authorarray)) {
					if ($count++ < $showcount)
						$dd .= $author.",".$commits.":";

					$commitcount += $commits;
				}
				reset($authorarray);

				?>
				<h1 align=center><?=$chart_subject?></h1>
				<center>
					<a href="horizbar.php?
						subject=<?=urlencode($chart_subject)?>&
						data=<?=$dd?>">
						<img class="graph" src="horizbar.php?
							subject=<?=urlencode($chart_subject)?>&
							data=<?=$dd?>" border=0 align=top>
					</a>
				</center>
					
				<table border=0 width=100% height=100% cellpadding=0 cellspacing=0>
					<tr>
						<td valign=top>
							<div class="maincontents1_right">
								<table cellpadding=0 cellspacing=0 class="chart_view_legend_table">
									<tr>
										<td class="chart_view_legend_table_subject1">Author (Total <?=$authorcount?>)</td>
										<td class="chart_view_legend_table_subject2">Commits (Total <?=$commitcount?>)</td>
									</tr>		
									<?
									while (list($author,$commits) = each($authorarray)) {
										?>
										<tr>
											<td class="chart_view_legend_table_content1">
												<?$index++; echo $index." "; $author = substr($author, 0, 24); echo $author;?>
											</td>
											<td class="chart_view_legend_table_content2"><?=$commits?></td>
										</tr>
										<?
									}
									?>
								</table>
							</div>
						</td>
					</tr>
				</table>
				<?	
			}else {
				?>
				<div class="chart_view_chart">
					No contents for display.<br>
				</div>
				<?	
			}
		}		
	}	
	////////////////////////////////////////// Author by project release (6th page)	
	else if ($chartnumber == 6) {

		if ($_GET['submit']) {

			$chart_subject = preg_replace("/Release\)/", "release ".$_GET['chart_parameter_ver'].")", $chart_subject6);

			// Retrieve the authors for the wanted release
			$authorarray = authors_by_release($_GET['chart_parameter_ver']);

			// Number of entries in the array
			$authorcount = count($authorarray);

			// Number of authors we want to show in the graph
			// FIXME: We can't rely on limit passed to piegraph as sometimes the number of authors
			//        is to much to handle (see BUG 1807418).
			//        This also means we don't have a 'etc' in the graph anymore.
			$showcount = 20;

			if ($authorcount > 0) {

				$count = 0;
				$commitcount = 0;
				while (list($author,$commits) = each($authorarray)) {
					if ($count++ < $showcount)
						$dd .= $author.",".$commits.":";

					$commitcount += $commits;
				}
				reset($authorarray);

				?>
				<h1 align=center><?=$chart_subject?></h1>
				<center>
					<a href="horizbar.php?
							subject=<?=urlencode($chart_subject)?>&
							data=<?=$dd?>">
						<img class="graph" src="horizbar.php?
							subject=<?=urlencode($chart_subject)?>&
							data=<?=$dd?>" border=0 align=top>
					</a>
				</center>
	
				<table border=0 width=100% height=100% cellpadding=0 cellspacing=0>
					<tr>
						<td valign=top>
							<div class="maincontents1_right">
								<table cellpadding=0 cellspacing=0 class="chart_view_legend_table">
									<tr>
										<td class="chart_view_legend_table_subject1">Author (Total <?=$authorcount?>)</td>
										<td class="chart_view_legend_table_subject2">Commits (Total <?=$commitcount?>)</td>
									</tr>		
									<?
									while (list($author,$commits) = each($authorarray)) {
										?>
										<tr>
											<td class="chart_view_legend_table_content1">
												<?$index++; echo $index." "; $author = substr($author, 0, 24); echo $author;?>
											</td>
											<td class="chart_view_legend_table_content2"><?=$commits?></td>
										</tr>
										<?
									}
									?>
								</table>
							</div>
						</td>
					</tr>
				</table>
				<?		
			} else {
				?>
				<div class="chart_view_chart">
					No contents for display.<br>
				</div>
				<?
			}
		}
	}
	////////////////////////////////////////// Sub Directory Ratio by Date or Release (7th and 12th Page)
	else if ($chartnumber == 7 || $chartnumber == 12) {

		if ($_GET['submit']) {

			$chartlevel = $_GET['chart_level'];

			if (!$chartlevel)
				$chartlevel = 1;

			if ($chartnumber == 7) {

				if ($_GET['chart_parameter2_year'] && !$_GET['chart_parameter2_month']) {

					// We have only been given the year
					$startdate = gmmktime(0, 0, 0, 1, 1, $_GET['chart_parameter2_year']);
					$enddate = gmmktime(0, 0, 0, 1, 1, $_GET['chart_parameter2_year'] + 1);
					$chart_subject = preg_replace("/\(Date\)/", "(".$_GET['chart_parameter2_year'].")", $chart_subject7);

				} else if($_GET['chart_parameter2_year'] && $_GET['chart_parameter2_month']) {

					// We have been given a month an a year
					$startdate = gmmktime(0, 0, 0, $_GET['chart_parameter2_month'], 1, $_GET['chart_parameter2_year']);
					$enddate = gmmktime(0, 0, 0, $_GET['chart_parameter2_month'] + 1, 1, $_GET['chart_parameter2_year']);
					$chart_subject = preg_replace("/\(Date\)/","(".gmdate("F", $startdate)." ".$_GET['chart_parameter2_year'].")", $chart_subject7);

				}else{}

				$changesarray = directory_changes_by_date($startdate, $enddate, $chartlevel, $_GET['chart_level_sub']);

			} else {

				$chart_subject = preg_replace("/Release\)/", "release ".$_GET['chart_parameter_ver'].")", $chart_subject12);
				$changesarray = directory_changes_by_release($_GET['chart_parameter_ver'], $chartlevel, $_GET['chart_level_sub']);

			}

			$totalcount = 0;
			while (list($directory, $info) = each($changesarray)) {

				$dd .= $directory.",".$info[Changes].":";
				$totalcount += $info[Changes];
			}
			reset($changesarray);

			if($totalcount>0){
				?>
				<h1 align=center><?=$chart_subject?></h1>
				<center>
					<a href="./piegraph.php?
						subject=<?=urlencode($chart_subject)?>&
						data=<?=$dd?>&
						limit=7">
						<img class="graph" src="./piegraph.php?
							subject=<?=urlencode($chart_subject)?>&
							data=<?=$dd?>&
							limit=7" border=0 align=top>
					</a>
					<br><br>
					<table border=0 width=600px cellpadding=0 cellspacing=0>
						<th width=60px height=30px align="left">Category</th>
						<th width=100px align="left">Changes to files (<?=$totalcount?>)</th>
						<?if($chartlevel!=1){?>
						<tr>
							<td height=30px colspan=2>
							<span onclick="history.go(-1);" style='cursor:pointer'><u>Back</u></span>
							</td>
						</tr>			
						<?}

						while (list($directory, $info) = each($changesarray)) {
							?>
							<tr>
								<td>
									<? if (($info[SubDirectories] > 1) || ($info[SubDirectories] == 1 && $info[ChangesInDir])) {
										?>
										<a	href="chart.php?
											chart_parameter1=<?=$chartnumber?>&
											chart_parameter2_year=<?=$_GET['chart_parameter2_year']?>&
											chart_parameter2_month=<?=$_GET['chart_parameter2_month']?>&
											submit=1&
											chart_level=<?=($chartlevel+1)?>&
											chart_parameter_ver=<?=$_GET['chart_parameter_ver']?>&
											chart_level_sub=<?=$_GET['chart_level_sub']."/".$directory?>" class="underline"><?
									}?>
									<?=$directory;?>
									</a>
								</td>
								<td><?=$info[Changes]?></td>
							</tr>
						<?}?>
					</table>
				</center>
			<?	
			}else{
				?>
					No contents for display.<br>
			<?}
		}
	}
	////////////////////////////////////////// Frequency of project release(8th Page)
	else if ($chartnumber == 8) {

		if ($_GET['submit']) {

			// Retrieve the commits
			$releasearray = release_frequency($showcount, $page);

			while (list($release, $duration) = each($releasearray)) {
				$dd.=$release.",".$duration.":";
			}

			// FIXME: Calculate the maximum to be passed to the chart or find other
			// means to have the graph have a correct scale for the whole project

			?>
			<h1 align=center><?=$chart_subject8?></h1>
			<center>
				<table border=0 cellpadding=0 cellspacing=0>
					<tr>
						<?if (TRUE /* FIXME: Don't show the left arrow if not needed */) {
							?>
							<td valign=bottom><a href="<?=$_SERVER['PHP_SELF']?>?
								chart_parameter1=<?=$chartnumber?>&
								showcount=<?=$showcount?>&
								submit=1&
								page=<?echo $page+1;?>"
								title="Show previous releases">
								<font size=5><</font>
								</a><br>
							</td>
							<?
						}?>
						<td><a href="./bargraph.php?
								subject=<?=urlencode($chart_subject8)?>&
								data=<?=$dd?>&
								format=day&
								barcolor=red">
								<img class="graph" src="./bargraph.php?
									subject=<?=urlencode($chart_subject8)?>&
									data=<?=$dd?>&
									format=day&
									barcolor=red" border=0 align=top>
							</a>
						</td>
						<?if ($page > 0) {
							?>
							<td valign=bottom><a href="<?=$_SERVER['PHP_SELF']?>?
								chart_parameter1=<?=$chartnumber?>&
								showcount=<?=$showcount?>&
								submit=1&
								page=<?echo $page-1;?>"
								title="Show next releases">
								<font size=5>></font>
								</a><br>
							</td>
							<?
						}?>
					</tr>
				</table>
			</center>
			<?	
		}	
	}
	////////////////////////////////////////// Commits per project release (9th Page)
	else if ($chartnumber == 9) {

		if ($_GET['submit']) {

			$commitarray = commits_per_release($showcount, $page);

			while (list($release,$commits) = each($commitarray)) {
				$dd .= $release.",".$commits.":";
			}

			// FIXME: Calculate the maximum to be passed to the chart or find other
			// means to have the graph have a correct scale for the whole project

			?>
			<h1 align=center><?=$chart_subject9?></h1>
			<center>
				<table border=0 cellpadding=0 cellspacing=0>
					<tr>
						<?if (TRUE /* FIXME: Don't show the left arrow if not needed */) {
							?>
							<td valign=bottom><a href="<?=$_SERVER['PHP_SELF']?>?
								chart_parameter1=<?=$chartnumber?>&
								showcount=<?=$showcount?>&
								submit=1&
								page=<?echo $page+1;?>"
								title="Show previous releases">
								<font size=5><</font>
								</a><br>
							</td>
							<?
						}?>
						<td><a href="./bargraph.php?
							subject=<?=urlencode($chart_subject9)?>&
							data=<?=$dd?>&">
							<img class="graph" src="./bargraph.php?
								subject=<?=urlencode($chart_subject9)?>&
								data=<?=$dd?>" border=0 align=top>
							</a>
						</td>
						<?if ($page > 0) {
							?>
							<td valign=bottom><a href="<?=$_SERVER['PHP_SELF']?>?
								chart_parameter1=<?=$chartnumber?>&
								showcount=<?=$showcount?>&
								submit=1&
								page=<?echo $page-1;?>"
								title="Show next releases">
								<font size=5>></font>
								</a><br>
							</td>
							<?
						}?>
					</tr>
				</table>
			</center>
			<?
		}
	}
	///////////////////////////////////////////// Commits per hour by project release (10th Page)
	else if ($chartnumber == 10) {

		if ($_GET['submit']) {

			$commitarray = hourly_commits_per_release($showcount, $page);

			while (list($release,$commitsperhour) = each($commitarray)) {
				$dd .= $release.",".$commitsperhour.":";
			}

			?>	
			<h1 align=center><?=$chart_subject10?></h1>
			<center>
				<table border=0 cellpadding=0 cellspacing=0>
					<tr>
						<?if (TRUE /* FIXME: Don't show the left arrow if not needed */) {
							?>
							<td valign=bottom><a href="<?=$_SERVER['PHP_SELF']?>?
								chart_parameter1=<?=$chartnumber?>&
								showcount=<?=$showcount?>&
								submit=1&
								page=<?echo $page+1;?>"
								title="Show previous releases">
								<font size=5><</font>
								</a><br>
							</td>
							<?
						}?>
						<td><a href="./bargraph.php?
							subject=<?=urlencode($chart_subject10)?>&
							data=<?=$dd?>&
							float=true">
							<img class="graph" src="./bargraph.php?
								subject=<?=urlencode($chart_subject10)?>&
								data=<?=$dd?>&
								float=true"
								border=0 align=top>
							</a>
						</td>
						<?if ($page > 0) {
							?>
							<td valign=bottom><a href="<?=$_SERVER['PHP_SELF']?>?
								chart_parameter1=<?=$chartnumber?>&
								showcount=<?=$showcount?>&
								submit=1&
								page=<?echo $page-1;?>"
								title="Show next releases">
								<font size=5>></font>
								</a><br>
							</td>
							<?
						}?>
					</tr>
				</table>
			</center>
			<?
		}
	}	
	////////////////////////////////////////// Number of authors per project release (11th page)
	else if ($chartnumber == 11) {

		if ($_GET['submit']) {

			$releasearray = authorcount_by_release($showcount, $page);

			while (list($release,$authors) = each($releasearray)) {
				$dd .= $release.",".$authors.":";
			}

			?>
			<h1 align=center><?=$chart_subject11?></h1>
			<center>
				<table border=0 cellpadding=0 cellspacing=0>
					<tr>
						<?if (TRUE /* FIXME: Don't show the left arrow if not needed */) {
							?>
							<td valign=bottom><a href="<?=$_SERVER['PHP_SELF']?>?
								chart_parameter1=<?=$chartnumber?>&
								showcount=<?=$showcount?>&
								submit=1&
								page=<?echo $page+1;?>"
								title="Show previous releases">
								<font size=5><</font>
								</a><br>
							</td>
							<?
						}?>
						<td><a href="./bargraph.php?
							subject=<?=urlencode($chart_subject11)?>&
							data=<?=$dd?>">
							<img class="graph" src="./bargraph.php?
								subject=<?=urlencode($chart_subject11)?>&
								data=<?=$dd?>" border=0 align=top>
							</a>
						</td>
						<?if ($page > 0) {
							?>
							<td valign=bottom><a href="<?=$_SERVER['PHP_SELF']?>?
								chart_parameter1=<?=$chartnumber?>&
								showcount=<?=$showcount?>&
								submit=1&
								page=<?echo $page-1;?>"
								title="Show next releases">
								<font size=5>></font>
								</a><br>
							</td>
							<?
						}?>
					</tr>
				</table>
			</center>
			<?		
		}
	}
		
	?>
</div>
	
<script>
	view_month1();view_option(<?=$_GET['chart_parameter1']?>);
</script>

<?
include"footer.php"
?>
