<?

include "header.php";
include "include/libdb.php";
include "include/libgather.php";

$projectname = getprojectconfig("GIT_PROJECT_NAME");

dbconnect();
// Update the database if needed
updatedb();

$dbversion_current = checkdbversion();
// FIXME : We should probably not generate charts when $dbversion_current < 3 as libgather.php
// doesn't check that anymore. Put a message up that states that the DB should be upgraded manually
// as the automatic upgrade didn't work.

////////////////////////////////////////
// Recent Changeset Flow (Day)

$chart_subject1 = "Number of changesets (Day)";

// Number of days to show in the graph
$showcount = 30;
// On this indexpage we only show the last page
$page = 0;

// Retrieve the commits 
$commitarray = commits_per_day($showcount, $page);

while (list($day, $commits) = each($commitarray)) {
	$dd1.=gmdate("d",$day).",".$commits.":";
}

///////////////////////////////////////////////
// Recent changeset flow (Month)

$chart_subject2 = "Number of changesets (Month)";

// Number of months to show in the graph
$showcount = 12;
// On this indexpage we only show the last page
$page = 0;

// Retrieve the commits 
$commitarray = commits_per_month($showcount, $page);

while (list($month, $commits) = each($commitarray)) {
	$dd2.=gmdate('M/y', $month).",".$commits.":";
}

///////////////////////////////////////////
// Frequency of release per project version

$chart_subject3 = "$projectname release cycle duration";

// Number of releases to show in the graph
$showcount = 7;
// On this indexpage we only show the last page
$page = 0;

// Retrieve the release frequency 
$releasearray = release_frequency($showcount, $page);

// FIXME: x-labels can overlap, should we cut the labels or should 
// the graph generation take care of this?

while (list($release, $duration) = each($releasearray)) {
	$dd3.=$release.",".$duration.":";
}

///////////////////////////////////////////////
// Top Author for latest release of the project

$lastrelease = last_release();
$chart_subject4= "Top contributor in $projectname release $lastrelease";

// Retrieve the authors for the last release
$authorarray = authors_by_release($lastrelease);

// How many authors do we want to see in the graph
$showcount = 10;

$count = 0;
while (list($author, $commits) = each($authorarray)) {

	if ($count >= $showcount)
		break;

	$count++;
	$dd4.=$author.",".$commits.":";
}

///////////////////////////
// Top contributor's domain

$lastrelease = last_release();
$chart_subject5 = "Top contributor's domain ($projectname release $lastrelease)";

// Retrieve the domains for the last release
$domainarray = domains_by_release($lastrelease);

// How many domains do we want to see in the graph
$showcount = 20;

$count = 0;
while (list($domain, $commits) = each($domainarray)) {

	if ($count >= $showcount)
		break;

	$count++;
	$dd5.=$domain.",".$commits.":";
}

	///////////////////////////////////////////////
	// The actual page

		$MainArticle1[subject]="What is GitStat?";
		$MainArticle1[subjectlink]="./";
		$MainArticle1[contents]="Gitstat is an Open Source, web-based git statistics interface. You can browse up-to-date statistics information, search and view changesets(patchs) status, monitor git tree.";

		$MainArticle2[subject]="Recent ChangeLog";
		$MainArticle2[subjectlink]="./changelog-find.php";

		$MainArticle3[subject]="Statistics";
		$MainArticle3[subjectlink]="./chart.php";

	?>
				<!--MainArticle 1-->
				<h2><a href="<?=$MainArticle1[subjectlink]?>"><?=$MainArticle1[subject]?></a></h2>
				<p ><?=$MainArticle1[contents]?></p><!--id=mainarticle-->
				<p class="date"><img src="images/timeicon.gif" alt="" /></p>
				
				<!--MainArticle 2-->	
				<h2><a href="<?=$MainArticle2[subjectlink]?>"><?=$MainArticle2[subject]?></a></h2>
				<p>

				<div id="shortlog">	
				<?

				$search_query="SELECT COUNT(*) FROM ChangeLog";
				$total=mysql_result(mysql_query($search_query),0,0);

				if($total>0){

					echo "<table width=660px border=0 cellpadding=0 cellspacing=0><tr><td>&nbsp;</td></tr>";

					// Number of commits to show
					$showcommits = 16;

					$query="SELECT `commit`, `subject`, `author`, `commitdate`
						FROM `ChangeLog`
						ORDER BY `commitdate` DESC, `authordate` DESC
						LIMIT $showcommits";
					$result=mysql_query($query);

					while($data=@mysql_fetch_array($result)){

						$author = preg_replace("/(.*) <(.*)>/","$1",$data['author']);
						$mail = preg_replace("/(.*) <(.*)>/","$2",$data['author']);
						$subject = preg_replace("/\"/","'",$data['subject']);
						?>
						<tr>
							<td width=110px align="center" title="<?=date("T");?> : <?=date('y/m/d H:i',$data['commitdate'])?>"><font color=#232323><?=date("y/m/d H:i",$data['commitdate']-date("Z"))?></font></td>
							<td width=440px <?if (strlen($subject) > 60) echo "title=\"".$subject."\"";?>>&nbsp;
								<a href='./commit-detail.php?commit=<?=$data['commit']?>'>
									<b><? echo cut_str($subject, 60);?></b>
								</a>
							</td>
							<td width=110px <?if (strlen($author) > 14) echo "title=\"".$author."\"";?>>
								<a href='./sendmail.php?mail=<?=$mail?>&commit=<?=$data['commit']?>'>
									<i><? echo cut_str($author, 14);?></i>
								</a>
							</td>
						</tr>
						<?
					}
					echo "</table>";
				}else{
					?>
						<table width=100% border=1 cellpadding=0 cellspacing=0>
							<tr>
								<td height=720px colspan=3 align=center style='font-size:20px'><b>No result</b>
								</td>
							</tr>
						</table>
					<?
				}
			?>
			
			</div>	

				</p>
				<p class="date"><img src="images/timeicon.gif" alt="" /></p>

				<!--MainArticle 3-->
				<h2><a href="<?=$MainArticle3[subjectlink]?>"><?=$MainArticle3[subject]?></a></h2>

				<!--show recent changset Flow-->
				<h1 align=center ><?=$chart_subject1?></h1>
					<a href="./linegraph.php?
								subject=<?=urlencode($chart_subject1)?>&
								data=<?=$dd1?>&
								linecolor=red&
								hidezero=true">
					<center>
					<img class="graph" src="./linegraph.php?
								subject=<?=urlencode($chart_subject1)?>&
								data=<?=$dd1?>&
								linecolor=red&
								hidezero=true" border=0 align=top>
					</center>
					</a>	
				<!-- show recent chageset Flow(Month)-->
				<h1 align=center><?=$chart_subject2?></h1>
					<a href="./bargraph.php?
								subject=<?=urlencode($chart_subject2)?>&
								data=<?=$dd2?>&
								barcolor=red">
					<center>
					<img class="graph" src="./bargraph.php?
								subject=<?=urlencode($chart_subject2)?>&
								data=<?=$dd2?>&
								barcolor=red" border=0 align=top>
					</center>
					</a>
				<!-- Top Author for currnet Kernel-->
				<h1 align=center><?=$chart_subject4?></h1>
					<a href="horizbar.php?
								subject=<?=urlencode($chart_subject4)?>&
								data=<?=$dd4?>">
					<center>
					<img class="graph" src="horizbar.php?
								subject=<?=urlencode($chart_subject4)?>&
								data=<?=$dd4?>" border=0 align=top></a>
					</center>
					</a>
				<!-- Top contributor's domain-->
				<h1 align=center><?=$chart_subject5?></h1>
					<center><a href="piegraph.php?
							subject=<?=urlencode($chart_subject5)?>&
							data=<?=$dd5?>&
							order=true">
					<img class="graph" src="piegraph.php?
							subject=<?=urlencode($chart_subject5)?>&
							data=<?=$dd5?>&
							order=true" border=0 align=top></a>
					</center>
				<!-- Frequency of Kernel Release per kernel ver -->
				<h1 align=center><?=$chart_subject3?></h1>
					<a href="./bargraph.php?
								subject=<?=urlencode($chart_subject3)?>&
								data=<?=$dd3?>&
								format=day">
					<center>
					<img class="graph" src="./bargraph.php?
								subject=<?=urlencode($chart_subject3)?>&
								data=<?=$dd3?>&
								format=day" border=0 align=top>
					</center>
					</a>	

<?	include"footer.php"?>
