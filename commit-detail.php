<?

include"header.php";

$projectparams = getprojectconfig();
$gitcommand = $projectparams['GIT_EXE_PATH']."/git ".$projectparams['GIT_PATH'];

$geshidir = $projectparams['GESHIDIR'];
include_once($geshidir.'/geshi.php');

dbconnect();

$query="select * from ChangeLog where commit='".$_GET['commit']."'";
$result=mysql_query($query);
while($data=mysql_fetch_array($result)){
	$subject=$data['subject'];
	$commit=$data['commit'];		
	$parents=$data['parents'];
	$parents=split(" ",$parents);
	$parents_cnt=count($parents);
	$author=str_replace("<","&lt;",$data['author']);
	$author=str_replace(">","&gt;",$author);
	$content=str_replace("\n","</p><p style='height:19px;border-bottom:1px dotted #dddddd'>",$data['content']);
	$authordate_kst=date("Y/m/d H:i",$data['authordate']);
	$authordate_utc=date("Y/m/d H:i",$data['authordate']-date("Z"));
	$commitdate_kst=date("Y/m/d H:i",$data['commitdate']);
	$commitdate_utc=date("Y/m/d H:i",$data['commitdate']-date("Z"));

	$cversion=@mysql_result(mysql_query("select max(no) from v_tag"),0,0);
	if($cversion<$data['version']){
		$ckversion=@mysql_result(mysql_query("select name from v_tag where no=".$cversion),0,0);
		$kversion="Changelog Feature after <font color=blue> ".$ckversion."</font> BaseLine";
	}else
		$kversion=@mysql_result(mysql_query("select name from v_tag where no='".$data['version']."'"),0,0);
}

dbclose();

?>
	<div>
		<h2><a href="<?=$_SERVER['PHP_SELF']?>">Log</a></h2>
	</div>	
	<div>
		<table border=0 height=100% width=100% cellpadding=0 cellspacing=0>
		<tr height=28px id="commit_view_subject">
			<td colspan=2 >&nbsp;&nbsp;<?=$subject?></td>
			<? $mail = preg_replace("/(.*) &lt;(.*)&gt;/","$2",$author); ?>
		</tr>
		<tr>
			<td>
			<table  border=0 width=100% cellpadding=2 cellspacing =2 >
				<tr>
				<td>author  </td><td><a href='./sendmail.php?mail=<?=$mail?>&commit=<?=$commit?>' class='mail2author'><?=$author?></a></td>
				</tr>
				<tr>
				<td> </td><td> <?=$authordate_utc?>&nbsp;&nbsp;(<?=date("T");?> : <?=$authordate_kst?>)</a></td>
				</tr>
				<tr>
				<td>commit      </td><td><?=$commit?></td>
				</tr>
				<tr>
				<td>  </td><td><?=$commitdate_utc?>&nbsp;&nbsp;(<?=date("T");?> : <?=$commitdate_kst?>)</a></td>
				</tr>
				<? for($i=0;$i<$parents_cnt;$i++){?>
				<tr><td>parents  </td><td><a href='./commit-detail.php?commit=<?=$parents[$i]?>'><?=$parents[$i]?></a></td>
				</tr>
				<?}?>
				<tr><td>Location </td><td><?=$kversion?></td>
				</tr>
			</table>
			</td>
		</tr>
		<tr height=620px>
			<td valign=TOP>
				<div style="font-size:12px; font-family: Courier New;word-wrap: break-word;border:1px solid #dddddd; padding:18px;">
					<p style='height:10px;border-bottom:1px dotted #dddddd;'><?=$content?></p>
				</div>
				<div id='commit_view_menu'>
					<a href="changelog-index.php"><img src='./images/viewlist_button.gif' alt='list'/></a>
				</div>
				<div id='commit_view_files'>					
					<?
						$filenum = 0;
						$sub=1;
						$handle=popen($gitcommand." diff-tree $commit -r","r");
						while(!feof($handle)){
							$buffer = fgets($handle);
							if(preg_match(
										"/^:([0-7]{6}) ([0-7]{6}) ([0-9a-fA-F]{40}) ([0-9a-fA-F]{40}) (.)([0-9]{0,3})\t(.*)$\n/",
										$buffer)){
								$buffer = preg_replace(
										"/^:([0-7]{6}) ([0-7]{6}) ([0-9a-fA-F]{40}) ([0-9a-fA-F]{40}) (.)([0-9]{0,3})\t(.*)$\n/",
										"$7",$buffer);			
								echo "<a href='#diff".$sub++."'>".$buffer."</a><br>";
								$filenum++;
							}
						}
					?>
					<p style="font-size:12px"><i><? echo $filenum ?> files changed</i></p><br>
					<br>
				</div>
				<div id="commit_view_contents2">
					<?
						$sub=0;
						include_once($geshidir.'/geshi.php');
						$handle=popen($gitcommand." diff-tree $commit --patch-with-raw","r");
						while(!feof($handle)) {
							$buffer = fgets($handle);
							$buffer = preg_replace("/([0-9a-fA-F]{40})\n/","",$buffer);
							$buffer = preg_replace(
									"/^:([0-7]{6}) ([0-7]{6}) ([0-9a-fA-F]{40}) ([0-9a-fA-F]{40}) (.)([0-9]{0,3})\t(.*)$\n/",
									"",$buffer);				
							if(preg_match("/diff --git (.*)\n/",$buffer)){
								$diff_sub = "diff --git ".preg_replace("/diff --git (.*)\n/","$1",$buffer);
								$geshi =& new GeSHi($str, "diff");
								$diff_con = $geshi->parse_code(); 
								if($diff_con != "") echo $diff_con;//.=$buffer;
								$str="";
								$sub++;
								?>
									<p class="diff_sub" id="diff<?=$sub?>"><?=$diff_sub?></p>
								<?
							}
							$buffer = preg_replace(
									"/diff --git (.*)\n/",
									"",$buffer);
							$str.=$buffer;
							ob_flush();
							flush();
						}
						
						$geshi = new GeSHi($str, "diff");
						$diff_con = $geshi->parse_code(); 
						if($diff_con != "") echo $diff_con;//.=$buffer;
						//echo $diff_con;
					?>		
				</div>
			</td>
		</tr>
		</table>
	</div>

<?

include"footer.php"

?>
