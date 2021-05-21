<?

include"header.php"

?>
		<div>
			<h2><a href="<?=$_SERVER['PHP_SELF']?>">Tags</a></h2>
		</div>
		<div id=tags>
			<table width=660px border=0 cellpadding=0 cellspacing=0>
			<?
				dbconnect();

				$query="select * from v_tag order by epoch desc ";//limit 30
				$result=mysql_query($query);
				while($data=mysql_fetch_array($result)){
					?>
					<tr>
						<td width=20px>
						</td>
						<td>
							<a href="./changelog-find.php?search_opt=5&search5=<?=$data['name']?>">
							<b><?=$data['name']?></b>
							</a>
						</td>
						<td align=right title="<?=date("T");?> : <?=date('Y/m/d H:i',$data['epoch'])?>"><?=date("Y/m/d H:i:s",$data['epoch']-date("Z"))?></td>
					</tr>
					<?
				}

				dbclose();

			?>
			</table>
		</div>
<?

include"footer.php"

?>

