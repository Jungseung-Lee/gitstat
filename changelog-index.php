<?
include"header.php";

$indexsize = 40;
dbconnect();
	
	?>
		<div>
			<h2><a href="<?=$_SERVER['PHP_SELF']?>">ChangeLog</a></h2>
		</div>
		<div id="shortlog">	
			<?
				if(!$_GET['page']) $page=1;
				else $page=$_GET['page'];
				$start=($page-1)*$indexsize;
				$last=$indexsize;
				
				$search_query="select count(*) from ChangeLog";
				$query="select * from ChangeLog order by commitdate desc limit $start,$last";
						
				$total=mysql_result(mysql_query($search_query),0,0);
				if($total>0){
					echo "<table width=660px border=0 cellpadding=0 cellspacing=0>";
					
					$result=mysql_query($query);
					while($data=@mysql_fetch_array($result)){
						$author=str_replace("<","&lt;",$data['author']);
						$author=str_replace(">","&gt;",$author);
						?>
						<tr>
							<td width=110px align="center" title="<?=date("T");?> : <?=date('y/m/d H:i',$data['commitdate'])?>"><font color=#232323><?=date("y/m/d H:i",$data['commitdate']-date("Z"))?></font></td>
							<td width=440px>&nbsp;
								<a href='./commit-detail.php?commit=<?=$data['commit']?>'>
									<b><? echo cut_str($data['subject'], 60);?></b>
								</a>
							</td>
							<td width=110px>
								<? $mail = preg_replace("/(.*) &lt;(.*)&gt;/","$2",$author); ?>
								<a href='./sendmail.php?mail=<?=$mail?>&commit=<?=$data['commit']?>'>
									<font color=black><i><? 	
											$data[author] = strtr($data[author] ,"<",".");
											echo cut_str($data[author] , 14);//;
										?></i>
									</font>
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
			
			<?
				if($total%$indexsize==0) $pages=intval($total/$indexsize);
				else  $pages=intval($total/$indexsize)+1;
				if($page<=3){ $pagef=1; $pagel=7; }
				else if($page>=$pages-3){
				  $pagef=$pages-7;
				  $pagel=$pages; }
				else {
				  $pagef=$page-3;
				  $pagel=$page+3;
				}
				if($pages<=7){ $pagef=1; $pagel=$pages; }
			 ?>
			 
		<div>
			<a href='./changelog-index.php?page=1<?=$s_flag?>'>
				<img src='./images/forworddot.gif' alt='' style='border:0'>
			</a>
				<?   
			if($page>1){
				$page10m=$page-1;
				?>
				<a href='./changelog-index.php?page=<?=$page10m?><?=$s_flag?>'>
				[Prev]
				</a>
				<?
			}
			if($page>10){
				$page10m=$page-10;
				?>
				<a href='./changelog-index.php?page=<?=$page10m?><?=$s_flag?>'>
				[-10]
				</a>
				<?
			}            
			for($i=$pagef;$i<=$pagel;$i++){
				?>
				<a href='./changelog-index.php?page=<?=$i?><?=$s_flag?>'>
				<?if($page==$i){ echo"<font size=4 color=blue><b>".$i."</b></font>";}else echo"$i";?>
				</a>
				<?
			}
			if($pages-$page>10){
				$page10p=$page+10;
				?>
				<a href='./changelog-index.php?page=<?=$page10p?><?=$s_flag?>'>
				[+10]
				</a>
				<?
			}
			if($pages-$page>0){
				$page10p=$page+1;
				?>
				<a href='./changelog-index.php?page=<?=$page10p?><?=$s_flag?>'>
				[Next]
				</a>
				<?
			}
				?>
				<a href='./changelog-index.php?page=<?=$pages?><?=$s_flag?>'>
					<img src='./images/ffdot.gif' alt='' style='border:0'>
				</a>
		</div>	 
		  
<?
include"footer.php";
?>

