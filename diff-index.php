<?

set_time_limit(0);
include"header.php";

$projectparams = getprojectconfig();
$gitcommand = $projectparams['GIT_EXE_PATH']."/git ".$projectparams['GIT_PATH'];

$geshidir = $projectparams['GESHIDIR'];
include_once($geshidir.'/geshi.php');

$diffdir = $projectparams['DIFFDIR'];

dbconnect();

?>
	<script language=javascript>
				function view_diff(con){
					var son_obj=document.getElementById(con);
					if(son_obj.style.display=="none"){
						son_obj.style.display="block";
					}else{
						son_obj.style.display="none";
					}
				}
				function call_son(son){
					for(i=1;i<20;i++){
						
						if(document.getElementById(son+"_"+i))
						{
							var son_obj=document.getElementById(son+"_"+i);
						
							if(son_obj.style.display=="none"){
								son_obj.style.display="block";
							}else{
								son_obj.style.display="none";
								if(diff_obj=document.getElementById(son+"_"+i+"_diff"))
									diff_obj.style.display="none";
								if(son_obj=document.getElementById(son+"_"+i+"_"+1)){
									if(son_obj.style.display=="block"){
										for(i2=1;i2<20;i2++){
											if(son_obj=document.getElementById(son+"_"+i+"_"+i2))
												son_obj.style.display="none";				
											if(diff_obj=document.getElementById(son+"_"+i+"_"+i2+"_diff"))
												diff_obj.style.display="none";
											if(son_obj=document.getElementById(son+"_"+i+"_"+i2+"_"+1)){
												if(son_obj.style.display=="block"){
													for(i3=1;i3<20;i3++){
														if(son_obj=document.getElementById(son+"_"+i+"_"+i2+"_"+i3))
															son_obj.style.display="none";			
														if(diff_obj=document.getElementById(son+"_"+i+"_"+i2+"_"+i3+"_diff"))
															diff_obj.style.display="none";
														if(son_obj=document.getElementById(son+"_"+i+"_"+i2+"_"+i3+"_"+1)){
															if(son_obj.style.display=="block"){
																for(i4=1;i4<20;i4++){
																	if(son_obj=document.getElementById(son+"_"+i+"_"+i2+"_"+i3+"_"+i4))
																		son_obj.style.display="none";				
																	if(diff_obj=document.getElementById(son+"_"+i+"_"+i2+"_"+i3+"_"+i4+"_diff"))
																		diff_obj.style.display="none";
																	if(son_obj=document.getElementById(son+"_"+i+"_"+i2+"_"+i3+"_"+i4+"_"+1)){
																		if(son_obj.style.display=="block"){
																			for(i5=1;i5<20;i5++){
																				if(son_obj=document.getElementById(son+"_"+i+"_"+i2+"_"+i3+"_"+i4+"_"+i5))
																					son_obj.style.display="none";				
																				if(diff_obj=document.getElementById(son+"_"+i+"_"+i2+"_"+i3+"_"+i4+"_"+i5+"_diff"))
																					diff_obj.style.display="none";
																				if(son_obj=document.getElementById(son+"_"+i+"_"+i2+"_"+i3+"_"+i4+"_"+i5+"_"+1)){
																					if(son_obj.style.display=="block"){
																						for(i6=1;i6<20;i6++){
																							if(son_obj=document.getElementById(son+"_"+i+"_"+i2+"_"+i3+"_"+i4+"_"+i5+"_"+i6))
																								son_obj.style.display="none";				
																							if(diff_obj=document.getElementById(son+"_"+i+"_"+i2+"_"+i3+"_"+i4+"_"+i5+"_"+i6+"_diff"))
																								diff_obj.style.display="none";
																						}
																					}
																				}
																			}
																		}
																	}
																}
															}
														}
													}
												}
											}
										}
									}
								}
							}
							
						}//if(!document.getElementById(son+"_"+i))
					}
				}
	</script>
	
	<div>
		<h2><a href="<?=$_SERVER['PHP_SELF']?>">DiffManager</a></h2>
	</div>
	Diffmanager shows the difference between tags for selected subcategory.<br>
	<br><br>
	<table width=100% height=100% border=0 cellpadding=0 cellspacing=0>
		<tr height=120px>
			<td valign=TOP>		
				<div>From:
					<form action="<?=$_SERVER['PHP_SELF']?>" method=GET>
					<select name=kernel_ver1 onchange="submit()">
						<option>Version</option>
						<?
							$pss=$gitcommand." branch";
							$handle=popen($pss,"r");
									
							while(!feof($handle)) {
								$buffer = trim(fgets($handle));
										
								if(trim($buffer) != "* master" && trim($buffer) !="" && trim($buffer)!="v_linux-2.6.10" && trim($buffer)!="v_linux-2.6.11" && trim($buffer) !="origin")
								{
									if($_GET['kernel_ver1']==$buffer)
										$select_s1="selected=\"selected\"";
									else
										$select_s1=" ";
									?>							
									<option value="<?=$buffer?>" <?=$select_s1?>><?=$buffer?></option>
									<?								
								}						
							}				
		
							pclose($handle);
						
							$result=mysql_query("select name from v_tag order by epoch desc");
							
							while($data=mysql_fetch_array($result)){
								$buffer = $data['name'];
							
								if(!empty($buffer) || $buffer!=$_GET['kernel_ver2'])
								{
									if($_GET['kernel_ver1']==$buffer)
										$select_s1="selected=\"selected\"";
									else
										$select_s1=" ";
										echo"<option value=\"$buffer\" $select_s1>$buffer</option>";
								}
							}
						?>
						<option value=v_linux-2.6.11 <?if($_GET['kernel_ver1']=="v_linux-2.6.11"){echo"selected=selected";}?>>v2.6.11</option>
						<option value=v_linux-2.6.10 <?if($_GET['kernel_ver1']=="v_linux-2.6.10"){echo"selected=selected";}?>>v2.6.10</option>
					</select>
						<input type=hidden name=kernel_ver2 value=<?=$_GET['kernel_ver2']?>>
						<input type=hidden name=subcategory_1 value=<?=$_GET['subcategory_1']?>>
						<input type=hidden name=subcategory_2 value=<?=$_GET['subcategory_2']?>>
					</form>
				</div>
				<div>To:
					<form action="<?=$_SERVER['PHP_SELF']?>" method=GET>
					<select name=kernel_ver2 onchange="submit()">
						<option>Version</option>
						<!--<option value="current" <?if($_GET['kernel_ver2']=="current"){echo"selected=selected";}?>>current version</option>-->
						<?
						$pss=$gitcommand." branch";
						$handle=popen($pss,"r");
								
						while(!feof($handle)){
							$buffer = trim(fgets($handle));
							if(trim($buffer) != "* master" && trim($buffer) !="" && trim($buffer)!="v_linux-2.6.10" && trim($buffer)!="v_linux-2.6.11" && trim($buffer) !="origin" ){
								if($_GET['kernel_ver2']==$buffer)
									$select_s1="selected=\"selected\"";
								else
									$select_s1=" ";
								?>							
								<option value="<?=$buffer?>" <?=$select_s1?>><?=$buffer?></option>
								<?								
							}						
						}				
	
						pclose($handle);
						
						
						$result=mysql_query("select name from v_tag order by epoch desc");
						while($data=mysql_fetch_array($result)){
							$buffer = $data['name'];
						
							if(!empty($buffer) || $buffer!=$_GET['kernel_ver1'] || $buffer>$_GET['kernel_ver1'])
							{
								if($_GET['kernel_ver2']==$buffer)
									$select_s1="selected=\"selected\"";
								else
									$select_s1=" ";
									echo"<option value=\"$buffer\" $select_s1>$buffer</option>";
							}
						}
						?>
						<option value=v_linux-2.6.11 <?if($_GET['kernel_ver2']=="v_linux-2.6.11"){echo"selected=selected";}?>>v2.6.11</option>
						<option value=v_linux-2.6.10 <?if($_GET['kernel_ver2']=="v_linux-2.6.10"){echo"selected=selected";}?>>v2.6.10</option>
					</select>
						<input type=hidden name=kernel_ver1	value=<?=$_GET['kernel_ver1']?>>
						<input type=hidden name=subcategory_1 value=<?=$_GET['subcategory_1']?>>
						<input type=hidden name=subcategory_2 value=<?=$_GET['subcategory_2']?>>
					</form>
				</div>
				<div>Subcategory
					<? if($_GET['kernel_ver2'] && $_GET['kernel_ver1']){ ?>
					<div class='maincontents1_center_diff_option_contents_item'>
						<form action="<?=$_SERVER['PHP_SELF']?>" method=GET>
						<select name=subcategory_1 onchange="submit()">
						<option value=SubCategory selected>SubCategory</option>
						<?
							$handle=popen($gitcommand." diff-tree --name-only ".$_GET['kernel_ver1']." ".$_GET['kernel_ver2'],"r");
							while(!feof($handle)) {
									$buffer = fgets($handle);
								$buffer = trim($buffer);
								
								if($_GET['subcategory_1']==$buffer)
									$select_1="selected";
								else
									$select_1=" ";
								
								if($buffer!="Kbuild" && $buffer!="MAINTAINERS" && $buffer!="Makefile" && $buffer!="CREDITS" && $buffer!=".gitignore" &&	$buffer!=".mailmap" && $buffer!="README" && $buffer!="COPYING" && $buffer!="REPORTING-BUGS" && $buffer!="") 
									echo "<option value=\"$buffer\" $select_1>$buffer</option>";
								
							}
		
							pclose($handle);
		
						?>
						</select>
						<input type=hidden name=kernel_ver1	value=<?=$_GET['kernel_ver1']?>>
						<input type=hidden name=kernel_ver2 value=<?=$_GET['kernel_ver2']?>>
						<input type=hidden name=subcategory_2 value=<?=$_GET['subcategory_2']?>>
						</form>
					</div>
					
					<?}
					if($_GET['subcategory_1']){ 
					?>
					
					<div>
						<form action="<?=$_SERVER['PHP_SELF']?>" method=GET>
							<select name=subcategory_2 onchange="submit()">
							<option value=SubCategory_2 selected>SubCategory2</option>
							<?
								$pss=$gitcommand." diff-tree -r ".$_GET['kernel_ver1']." ".$_GET['kernel_ver2']." | grep	".$_GET['subcategory_1']."/";
								$handle=popen($pss,"r");
								while(!feof($handle)) {
								
									$buffer = fgets($handle);
									$buffer = split("/",$buffer);
									$buffer = $buffer[1];
									$buffer = trim($buffer);
			
									if(($prebuf != $buffer) && ($buffer != ""))
									{						
										if($_GET['subcategory_2']==$buffer)
											$select_2="selected";
										else
											$select_2=" ";
									
										echo "<option value=\"$buffer\"	$select_2>$buffer</option>";
			
										$prebuf = $buffer;
									}else{
										
									}
									
								}
								
								echo "<option>".$ddd."</option>";
			
			
								pclose($handle);
							?>
							</select>
							
							<input type=hidden name=kernel_ver1	value=<?=$_GET['kernel_ver1']?>>
							<input type=hidden name=kernel_ver2 value=<?=$_GET['kernel_ver2']?>>
							<input type=hidden name=subcategory_1 value=<?=$_GET['subcategory_1']?>>
						</form>
					</div>
					<?}?>
				</div>
				<div>
					<form action=<?=$_SERVER['PHP_SELF']?> method=GET>
						<input type=submit name=diff_confirm value='Confirm'>
						<? if($_GET['kernel_ver1'] && $_GET['kernel_ver2'] && $_GET['confirm']){?>
						<input type=button name=diff_download value='Download diff file'
							onclick="location.replace('download.php?file=<?=$_GET['kernel_ver1']?>_<?=$_GET['kernel_ver2']?>_<?=$_GET['subcategory_1']?>_<?=$_GET['subcategory_2']?>_.diff')">
						<input type=button name=diff_short value='Open report'
							onclick="location.replace('diff_short.php?file=<?=$_GET['kernel_ver1']?>_<?=$_GET['kernel_ver2']?>_<?=$_GET['subcategory_1']?>_<?=$_GET['subcategory_2']?>_.diff')">
						<?}?>
						<input type=hidden name=kernel_ver1	value=<?=$_GET['kernel_ver1']?>>
						<input type=hidden name=kernel_ver2 value=<?=$_GET['kernel_ver2']?>>
						<input type=hidden name=subcategory_1 value=<?=$_GET['subcategory_1']?>>
						<input type=hidden name=subcategory_2 value=<?=$_GET['subcategory_2']?>>
						<input type=hidden name=confirm value=1>
					</form>
				</div>
			</td>
		</tr>
		<tr height=550px valign=top>
			<td>
	
				<div class='maincontents1_center_diff_result'>
				<?//echo  ($_GET['kernel_ver1'] && $_GET['kernel_ver2'] && $_GET['confirm']);?>	
			<? if($_GET['kernel_ver1'] && $_GET['kernel_ver2'] && $_GET['confirm']){?>
			<?  
					$existed=$_GET['kernel_ver1']."_".
							$_GET['kernel_ver2']."_".
							$_GET['subcategory_1']."_".
							$_GET['subcategory_2']."_".".diff";
					if(file_exists($diffdir."/".$existed) && (filesize($diffdir."/".$existed)>0)){
						mysql_query("UPDATE diffmanager SET count=count+1 WHERE diff_filename='".$existed."'");
					}
					else
					{
	
						$ctag2=split("_",$_GET['kernel_ver2']);
						//if($_GET['kernel_ver2']=="current")
						//	$kernel_ver2="";
						//else 
							$kernel_ver2=$_GET['kernel_ver2'];
			
						if($_GET['kernel_ver1'] && 
								$_GET['kernel_ver2'] && 
								$_GET['confirm'] && 
								($_GET['subcategory_1']=="SubCategory" || 
								 $_GET['subcategory_1']=="") &&
								($_GET['subcategory_2']=="SubCategory_2" ||
								 $_GET['subcategory_2']=="")
						  )
						{
							exec($gitcommand." diff ".
									$_GET['kernel_ver1']." ".
									$kernel_ver2." -- ".
									" >	".$diffdir."/".
									$_GET['kernel_ver1']."_".
									$_GET['kernel_ver2']."_".
									$_GET['subcategory_1']."_".
									$_GET['subcategory_2']."_".".diff");
						}else if($_GET['confirm'] &&
								($_GET['subcategory_1']!="SubCategory" ||
								 $_GET['subcategory_1']!="") &&
								($_GET['subcategory_2']=="SubCategory_2" ||
								 $_GET['subcategory_2']=="")
							){
							exec($gitcommand." diff ".
									$_GET['kernel_ver1']." ".
									$kernel_ver2." -- ".
									$_GET['subcategory_1'].
									" >	".$diffdir."/".
									$_GET['kernel_ver1']."_".
									$_GET['kernel_ver2']."_".
									$_GET['subcategory_1']."_".
									$_GET['subcategory_2']."_".".diff");
						}else if($_GET['confirm'] &&
								($_GET['subcategory_1']!="SubCategory" ||
								 $_GET['subcategory_1']!="") &&
								($_GET['subcategory_2']!="SubCategory_2" ||
								 $_GET['subcategory_2']!="")
							){
							exec($gitcommand." diff ".
									$_GET['kernel_ver1']." ".
									$kernel_ver2." -- ".
									$_GET['subcategory_1']."/".
									$_GET['subcategory_2'].
									" >	".$diffdir."/".
									$_GET['kernel_ver1']."_".
									$_GET['kernel_ver2']."_".
									$_GET['subcategory_1']."_".
									$_GET['subcategory_2']."_".".diff");
						}else{
							echo($gitcommand." diff ".
									$_GET['kernel_ver1']." ".
									$kernel_ver2." -- ".
									$_GET['subcategory_1']."/".
									$_GET['subcategory_2'].
									" >	".$diffdir."/".
									$_GET['kernel_ver1']."_".
									$_GET['kernel_ver2']."_".
									$_GET['subcategory_1']."_".
									$_GET['subcategory_2']."_".".diff");
						}
						
						
						if(!mysql_query("INSERT INTO diffmanager(diff_filename,diff_filesize,count) values('".
							$existed."','".filesize($diffdir."/".$existed)."','0')")) echo mysql_error();
					}
					$fcontents = fopen($diffdir."/".
									$_GET['kernel_ver1']."_".
									$_GET['kernel_ver2']."_".
									$_GET['subcategory_1']."_".
									$_GET['subcategory_2']."_".".diff","r");
					if($fcontents){
						while(!feof($fcontents)){
							$flines[] = fgets($fcontents,4096);
						}
						fclose($fcontents);
					}
					$flag=0;
					$i=0;
					while (list ($line_num, $line) = each ($flines)) {
						if(preg_match("/--- (.*)/",$line,$match)){
							$data[++$i][0]=$match[1];
						}
						if(preg_match("/\+\+\+ (.*)/",$line,$match2)){
							$data[$i][1]=$match2[1];
						}
						if(!preg_match("/--- (.*)/",$line) && !preg_match("/\+\+\+ (.*)/",$line) && !preg_match("/diff --git (.*)/",$line) && !preg_match("/index (.*)/",$line)){
							$data[$i][2].=addslashes($line);				
						}
					}
				
					$now=time();
	
					if(!mysql_query("CREATE TABLE `diff_".$now."` (
							  `no` int(255) NOT NULL auto_increment,
							  `opt` varchar(10) NOT NULL,
							  `sub1` varchar(50) NOT NULL,
							  `sub2` varchar(50) NOT NULL,
							  `sub3` varchar(50) NOT NULL,
							  `sub4` varchar(50) NOT NULL,
							  `sub5` varchar(50) NOT NULL,
							  `sub6` varchar(50) NOT NULL,
							  `sub7` varchar(50) NOT NULL,
							  `sub8` varchar(50) NOT NULL,
							  `sub9` varchar(50) NOT NULL,
							  `text` text NOT NULL,
							  PRIMARY KEY  (no))
					")) echo(mysql_error());
	
	
					for($m=0;$m<$i+1;$m++){
						if($data[$m][0]=="/dev/null"){
							preg_match("/b\/(.*)/",$data[$m][1],$temp);
							$ea=split("/",$temp[1]);
							$query="insert into diff_".$now."(opt,sub1,sub2,sub3,sub4,sub5,sub6,sub7,sub8,sub9,text) values('created',";
							for($l=0;$l<8;$l++){
								$query.="'".$ea[$l]."',";
							}
							$query.="'test','".$data[$m][2]."')";
							if(!mysql_query($query)) echo(mysql_error());
						}else if($data[$m][1]=="/dev/null"){
							preg_match("/a\/(.*)/",$data[$m][0],$temp);
							$ea=split("/",$temp[1]);
							$query="insert into diff_".$now."(opt,sub1,sub2,sub3,sub4,sub5,sub6,sub7,sub8,sub9,text) values('deleted',";
							for($l=0;$l<8;$l++){
								$query.="'".$ea[$l]."',";
							}
							$query.="'test','".$data[$m][2]."')";
							if(!mysql_query($query)) echo(mysql_error());
						}else{
							preg_match("/a\/(.*)/",$data[$m][0],$temp);
							$ea=split("/",$temp[1]);
							$query="insert into diff_".$now."(opt,sub1,sub2,sub3,sub4,sub5,sub6,sub7,sub8,sub9,text) values('modified',";
							for($l=0;$l<8;$l++){
								$query.="'".$ea[$l]."',";
							}
							$query.="'test','".$data[$m][2]."')";
							if(!mysql_query($query)) echo(mysql_error());
						}
					}
				?>
		
		<table  border=0 cellpadding=0 cellspacing=0>
					<?	
					// sub1 list
					$num=0;
					$sub1_list=array();
					$res=mysql_query("select sub1,text from diff_".$now."");
					while($data=mysql_fetch_array($res)){
						if(!in_array($data['sub1'],$sub1_list)){
							array_push($sub1_list,$data['sub1']);
							if($data['sub1']){
								$cnt_created=mysql_result(mysql_query(
											"select count(*) from diff_".$now." where opt='created' and sub1='".$data['sub1']."'"
											),0,0);
								$cnt_deleted=mysql_result(mysql_query(
											"select count(*) from diff_".$now." where opt='deleted' and sub1='".$data['sub1']."'"
											),0,0);
								$cnt_modified=mysql_result(mysql_query(
											"select count(*) from diff_".$now." where opt='modified' and sub1='".$data['sub1']."'"
											),0,0);
								echo "<tr id=".++$num." style='display:block'><td>";
								$subed=mysql_result(mysql_query("select count(*) from diff_".$now." where sub1='".$data['sub1']."'"),0,0);
								if($cnt_modified){
									if($subed<=1)
										echo"<p style='color:green;cursor:pointer' onclick=view_diff('".$num."_diff')>";
									else
										echo"<p style='color:green;cursor:pointer' onclick=call_son('".$num."')>";
									echo "+ ".$data['sub1'];
									echo "</p></td></tr>";
								}else if($cnt_created){
									if($subed<=1)
										echo"<p style='color:blue;cursor:pointer' onclick=view_diff('".$num."_diff')>";
									else
										echo"<p style='color:blue;cursor:pointer' onclick=call_son('".$num."')>";
									echo "+ ".$data['sub1'];
									echo "</p></td></tr>";
								}else if($cnt_deleted){
									if($subed<=1)
										echo"<p style='color:red;cursor:pointer' onclick=view_diff('".$num."_diff')>";
									else
										echo"<p style='color:red;cursor:pointer' onclick=call_son('".$num."')>";
									echo "+ ".$data['sub1'];
									echo "</p></td></tr>";
								}
								echo "<tr id=".$num."_diff style='display:none'><td class=diffvw>&nbsp;</td><td class=diff_view>";
								$geshi =& new GeSHi($data['text'], "diff");
								$diff_con = $geshi->parse_code(); 
								echo $diff_con;
								echo "</td></tr>";
							}
							// sub2_list	
							$sub2_list=array();			
							$num2=0;
							$res2=mysql_query("select sub2,text from diff_".$now." where sub1='".$data['sub1']."'");
							while($data2=mysql_fetch_array($res2)){
								if(!in_array($data2['sub2'],$sub2_list)){
									array_push($sub2_list,$data2['sub2']);
									if($data2['sub2']){
										$cnt_created=mysql_result(mysql_query(
													"select count(*) from diff_".$now." where opt='created' and sub1='".$data['sub1'].
													"' and sub2='".$data2['sub2']."'"
													),0,0);
										$cnt_deleted=mysql_result(mysql_query(
													"select count(*) from diff_".$now." where opt='deleted' and sub1='".$data['sub1'].
													"' and sub2='".$data2['sub2']."'"
													),0,0);
										$cnt_modified=mysql_result(mysql_query(
													"select count(*) from diff_".$now." where opt='modified' and sub1='".$data['sub1'].
													"' and sub2='".$data2['sub2']."'"
													),0,0);
										echo "\t<tr id=".$num."_".++$num2." style='display:none'><td class=diffvw>&nbsp;</td><td>";
										$subed2=mysql_result(mysql_query("select count(*) from diff_".$now." where sub1='".$data['sub1']."' and sub2='".$data2['sub2']."'"),0,0);
										if($cnt_modified){
											if($subed2<=1)
												echo"<p style='color:green;cursor:pointer' onclick=view_diff('".$num."_".$num2."_diff')>";
											else
												echo"<p style='color:green;cursor:pointer' onclick=call_son('".$num."_".$num2."')>";
											echo $data2['sub2'];
											echo "</td></tr>";
										}else if($cnt_created){
											if($subed2<=1)
												echo"<p style='color:blue;cursor:pointer' onclick=view_diff('".$num."_".$num2."_diff')>";
											else
												echo"<p style='color:blue;cursor:pointer' onclick=call_son('".$num."_".$num2."')>";
											echo $data2['sub2'];
											echo "</td></tr>";
										}else if($cnt_deleted){
											if($subed2<=1)
												echo"<p style='color:red;cursor:pointer' onclick=view_diff('".$num."_".$num2."_diff')>";
											else
												echo"<p style='color:red;cursor:pointer' onclick=call_son('".$num."_".$num2."')>";
											echo $data2['sub2'];
											echo "</td></tr>";
										}
											echo "<tr id=".$num."_".$num2."_diff style='display:none'><td class=diffvw>&nbsp;</td><td class=diffvw>&nbsp;</td><td class=diff_view>";
											$geshi =& new GeSHi($data2['text'], "diff");
											$diff_con = $geshi->parse_code(); 
											echo $diff_con;
											echo "</td></tr>";
										
									}
									// sub3_list					
									$sub3_list=array();
									$num3=0;
									$res3=mysql_query("select sub3,text from diff_".$now." where sub1='".$data['sub1']."' and sub2='".$data2['sub2']."'");
									while($data3=mysql_fetch_array($res3)){
										if(!in_array($data3['sub3'],$sub3_list)){
											array_push($sub3_list,$data3['sub3']);
											if($data3['sub3']){
												$cnt_created=mysql_result(mysql_query(
															"select count(*) from diff_".$now." where opt='created' and sub1='".$data['sub1'].
															"' and sub2='".$data2['sub2'].
															"' and sub3='".$data3['sub3']."'"
															),0,0);
												$cnt_deleted=mysql_result(mysql_query(
															"select count(*) from diff_".$now." where opt='deleted' and sub1='".$data['sub1'].
															"' and sub2='".$data2['sub2'].
															"' and sub3='".$data3['sub3']."'"
															),0,0);
												$cnt_modified=mysql_result(mysql_query(
															"select count(*) from diff_".$now." where opt='modified' and sub1='".$data['sub1'].
															"' and sub2='".$data2['sub2'].
															"' and sub3='".$data3['sub3']."'"
															),0,0);
												echo "\t\t<tr id=".$num."_".$num2."_".++$num3." style='display:none'><td class=diffvw>&nbsp;</td><td class=diffvw>&nbsp;</td><td>";
												$subed3=mysql_result(mysql_query("select count(*) from diff_".$now." where sub1='".$data['sub1']."' and sub2='".$data2['sub2']."' and sub3='".$data3['sub3']."'"),0,0);
												if($cnt_modified){
													if($subed3<=1)
														echo"<p style='color:green;cursor:pointer' onclick=view_diff('".$num."_".$num2."_".$num3."_diff')>";
													else
														echo"<p style='color:green;cursor:pointer' onclick=call_son('".$num."_".$num2."_".$num3."')>";
													echo $data3['sub3'];
													echo "</td></tr>";
												}else if($cnt_created){
													if($subed3<=1)
														echo"<p style='color:blue;cursor:pointer' onclick=view_diff('".$num."_".$num2."_".$num3."_diff')>";
													else
														echo"<p style='color:blue;cursor:pointer' onclick=call_son('".$num."_".$num2."_".$num3."')>";
													echo $data3['sub3'];
													echo "</td></tr>";
												}else if($cnt_deleted){
													if($subed3<=1)
														echo"<p style='color:red;cursor:pointer' onclick=view_diff('".$num."_".$num2."_".$num3."_diff')>";
													else
														echo"<p style='color:red;cursor:pointer' onclick=call_son('".$num."_".$num2."_".$num3."')>";
													echo $data3['sub3'];
													echo "</td></tr>";
												}
												echo "<tr id=".$num."_".$num2."_".$num3."_diff style='display:none'><td class=diffvw>&nbsp;</td><td class=diffvw>&nbsp;</td><td class=diffvw>&nbsp;</td><td class=diff_view>";
												$geshi =& new GeSHi($data3['text'], "diff");
												$diff_con = $geshi->parse_code(); 
												echo $diff_con;
												echo "</td></tr>";
											}
											// sub4_list
											$sub4_list=array();
											$num4=0;
											$res4=mysql_query(
													"select sub4,text from diff_".$now." where sub1='".$data['sub1'].
													"' and sub2='".$data2['sub2'].
													"' and sub3='".$data3['sub3']."'");
											while($data4=mysql_fetch_array($res4)){
												if(!in_array($data4['sub4'],$sub4_list)){
													array_push($sub4_list,$data4['sub4']);
													if($data4['sub4']){
														$cnt_created=mysql_result(mysql_query(
																	"select count(*) from diff_".$now." where opt='created' and sub1='".$data['sub1'].
																	"' and sub2='".$data2['sub2'].
																	"' and sub3='".$data3['sub3'].
																	"' and sub4='".$data4['sub4']."'"
																	),0,0);
														$cnt_deleted=mysql_result(mysql_query(
																	"select count(*) from diff_".$now." where opt='deleted' and sub1='".$data['sub1'].
																	"' and sub2='".$data2['sub2'].
																	"' and sub3='".$data3['sub3'].
																	"' and sub4='".$data4['sub4']."'"
																	),0,0);
														$cnt_modified=mysql_result(mysql_query(
																	"select count(*) from diff_".$now." where opt='modified' and sub1='".$data['sub1'].
																	"' and sub2='".$data2['sub2'].
																	"' and sub3='".$data3['sub3'].
																	"' and sub4='".$data4['sub4']."'"
																	),0,0);
														echo "\t\t\t<tr id=".$num."_".$num2."_".$num3."_".++$num4."  style='display:none'><td class=diffvw>&nbsp;</td><td class=diffvw>&nbsp;</td><td class=diffvw>&nbsp;</td><td>";
														$subed4=mysql_result(mysql_query("select count(*) from diff_".$now." where sub1='".$data['sub1']."' and sub2='".$data2['sub2']."' and sub3='".$data3['sub3']."' and sub4='".$data4['sub4']."'"),0,0);
														if($cnt_modified){
															if($subed4<=1)
																echo"<p style='color:green;cursor:pointer' onclick=view_diff('".$num."_".$num2."_".$num3."_".$num4."_diff')>";
															else
																echo"<p style='color:green;cursor:pointer' onclick=call_son('".$num."_".$num2."_".$num3."_".$num4."')>";
															echo $data4['sub4'];
															echo "</td></tr>";
														}else if($cnt_created){
															if($subed4<=1)
																echo"<p style='color:blue;cursor:pointer' onclick=view_diff('".$num."_".$num2."_".$num3."_".$num4."_diff')>";
															else
																echo"<p style='color:blue;cursor:pointer' onclick=call_son('".$num."_".$num2."_".$num3."_".$num4."')>";
															echo $data4['sub4'];
															echo "</td></tr>";
														}else if($cnt_deleted){
															if($subed4<=1)
																echo"<p style='color:red;cursor:pointer' onclick=view_diff('".$num."_".$num2."_".$num3."_".$num4."_diff')>";
															else
																echo"<p style='color:red;cursor:pointer' onclick=call_son('".$num."_".$num2."_".$num3."_".$num4."')>";
															echo $data4['sub4'];
															echo "</td></tr>";
														}
														echo "<tr id=".$num."_".$num2."_".$num3."_".$num4."_diff style='display:none'><td class=diffvw>&nbsp;</td><td class=diffvw>&nbsp;</td><td class=diffvw>&nbsp;</td><td class=diffvw>&nbsp;</td><td class=diff_view>";
														$geshi =& new GeSHi($data4['text'], "diff");
														$diff_con = $geshi->parse_code(); 
														echo $diff_con;
														echo "</td></tr>";
													}
													//sub5_list
													$sub5_list=array();
													$num5=0;
													$res5=mysql_query(
															"select sub5,text from diff_".$now." where sub1='".$data['sub1'].
															"' and sub2='".$data2['sub2'].
															"' and sub3='".$data3['sub3'].
															"' and sub4='".$data4['sub4'].
															"'");
													while($data5=mysql_fetch_array($res5)){
														if(!in_array($data5['sub5'],$sub5_list)){
															array_push($sub5_list,$data5['sub5']);
															if($data5['sub5']){
																$cnt_created=mysql_result(mysql_query(
																			"select count(*) from diff_".$now." where opt='created' and sub1='".$data['sub1'].
																			"' and sub2='".$data2['sub2'].
																			"' and sub3='".$data3['sub3'].
																			"' and sub4='".$data4['sub4'].
																			"' and sub5='".$data5['sub5']."'"
																			),0,0);
																$cnt_deleted=mysql_result(mysql_query(
																			"select count(*) from diff_".$now." where opt='deleted' and sub1='".$data['sub1'].
																			"' and sub2='".$data2['sub2'].
																			"' and sub3='".$data3['sub3'].
																			"' and sub4='".$data4['sub4'].
																			"' and sub5='".$data5['sub5']."'"
																			),0,0);
																$cnt_modified=mysql_result(mysql_query(
																			"select count(*) from diff_".$now." where opt='modified' and sub1='".$data['sub1'].
																			"' and sub2='".$data2['sub2'].
																			"' and sub3='".$data3['sub3'].
																			"' and sub4='".$data4['sub4'].
																			"' and sub5='".$data5['sub5']."'"
																			),0,0);
																echo "\t\t\t\t<tr id=".$num."_".$num2."_".$num3."_".$num4."_".++$num5." style='display:none'><td class=diffvw>&nbsp;</td><td class=diffvw>&nbsp;</td><td class=diffvw>&nbsp;</td><td class=diffvw>&nbsp;</td><td>";
																$subed5=mysql_result(mysql_query("select count(*) from diff_".$now." where sub1='".$data['sub1']."' and sub2='".$data2['sub2']."' and sub3='".$data3['sub3']."' and sub4='".$data4['sub4']."' and sub5='".$data5['sub5']."'"),0,0);
																if($cnt_modified){
																	if($subed5<=1)
																		echo"<p style='color:green;cursor:pointer' onclick=view_diff('".$data['sub1']."_".$num2."_".$num3."_".$num4."_".$num5."_diff')>";
																	else
																		echo"<p style='color:green;cursor:pointer' onclick=call_son('".$num."_".$num2."_".$num3."_".$num4."_".$num5."')>";
																	echo $data5['sub5'];
																	echo "</td></tr>";
																}else if($cnt_created){
																	if($subed5<=1)
																		echo"<p style='color:blue;cursor:pointer' onclick=view_diff('".$num."_".$num2."_".$num3."_".$num4."_".$num5."_diff')>";
																	else
																		echo"<p style='color:blue;cursor:pointer' onclick=call_son('".$num."_".$num2."_".$num3."_".$num4."_".$num5."')>";
																	echo $data5['sub5'];
																	echo "</td></tr>";
																}else if($cnt_deleted){
																	if($subed5<=1)
																		echo"<p style='color:red;cursor:pointer' onclick=view_diff('".$num."_".$num2."_".$num3."_".$num4."_".$num5."_diff')>";
																	else
																		echo"<p style='color:red;cursor:pointer' onclick=call_son('".$num."_".$num2."_".$num3."_".$num4."_".$num5."')>";
																	echo $data5['sub5'];
																	echo "</td></tr>";
																}
																echo "<tr id=".$num."_".$num2."_".$num3."_".$num4."_".$num5."_diff style='display:none'><td class=diffvw>&nbsp;</td><td class=diffvw>&nbsp;</td><td class=diffvw>&nbsp;</td><td class=diffvw>&nbsp;</td><td class=diffvw>&nbsp;</td><td class=diff_view>";
																$geshi =& new GeSHi($data5['text'], "diff");
																$diff_con = $geshi->parse_code(); 
																echo $diff_con;
																echo "</td></tr>";
															}
															//sub6_list
															$sub6_list=array();
															$num6=0;
															$res6=mysql_query(
																	"select sub6,text from diff_".$now." where sub1='".$data['sub1'].
																	"' and sub2='".$data2['sub2'].
																	"' and sub3='".$data3['sub3'].
																	"' and sub4='".$data4['sub4'].
																	"' and sub5='".$data5['sub5'].
																	"'");
															while($data6=mysql_fetch_array($res6)){
																if(!in_array($data6['sub6'],$sub6_list)){
																	array_push($sub6_list,$data6['sub6']);
																	if($data6['sub6']){
																		$cnt_created=mysql_result(mysql_query(
																					"select count(*) from diff_".$now." where opt='created' and sub1='".$data['sub1'].
																					"' and sub2='".$data2['sub2'].
																					"' and sub3='".$data3['sub3'].
																					"' and sub4='".$data4['sub4'].
																					"' and sub5='".$data5['sub5'].
																					"' and sub6='".$data6['sub6']."'"
																					),0,0);
																		$cnt_deleted=mysql_result(mysql_query(
																					"select count(*) from diff_".$now." where opt='deleted' and sub1='".$data['sub1'].
																					"' and sub2='".$data2['sub2'].
																					"' and sub3='".$data3['sub3'].
																					"' and sub4='".$data4['sub4'].
																					"' and sub5='".$data5['sub5'].
																					"' and sub6='".$data6['sub6']."'"
																					),0,0);
																		$cnt_modified=mysql_result(mysql_query(
																					"select count(*) from diff_".$now." where opt='modified' and sub1='".$data['sub1'].
																					"' and sub2='".$data2['sub2'].
																					"' and sub3='".$data3['sub3'].
																					"' and sub4='".$data4['sub4'].
																					"' and sub5='".$data5['sub5'].
																					"' and sub6='".$data6['sub6']."'"
																					),0,0);
																		echo "\t\t\t\t\t<tr id=".$num."_".$num2."_".$num3."_".$num4."_".$num5."_".++$num6."  style='display:none'><td class=diffvw>&nbsp;</td><td class=diffvw>&nbsp;</td><td class=diffvw>&nbsp;</td><td class=diffvw>&nbsp;</td><td class=diffvw>&nbsp;</td><td>";
																		$subed6=mysql_result(mysql_query("select count(*) from diff_".$now." where sub1='".$data['sub1']."' and sub2='".$data2['sub2']."' and sub3='".$data3['sub3']."' and sub4='".$data4['sub4']."' and sub5='".$data5['sub5']."' and sub6='".$data6['sub6']."'"),0,0);
																		if($cnt_modified){
																			if($subed6<=1)
																				echo"<p style='color:green;cursor:pointer' onclick=view_diff('".$num."_".$num2."_".$num3."_".$num4."_".$num5."_".$num6."_diff')>";
																			else
																				echo"<p style='color:green;cursor:pointer' onclick=call_son('".$num."_".$num2."_".$num3."_".$num4."_".$num5."_".$num6."')>";
																			echo $data6['sub6'];
																			echo "</td></tr>";
																		}else if($cnt_created){
																			if($subed6<=1)
																				echo"<p style='color:blue;cursor:pointer' onclick=view_diff('".$num."_".$num2."_".$num3."_".$num4."_".$num5."_".$num6."_diff')>";
																			else
																				echo"<p style='color:blue;cursor:pointer' onclick=call_son('".$num."_".$num2."_".$num3."_".$num4."_".$num5."_".$num6."')>";
																			echo $data6['sub6'];
																			echo "</td></tr>";
																		}else if($cnt_deleted){
																			if($subed6<=1)
																				echo"<p style='color:red;cursor:pointer' onclick=view_diff('".$num."_".$num2."_".$num3."_".$num4."_".$num5."_".$num6."_diff')>";
																			else
																				echo"<p style='color:red;cursor:pointer' onclick=call_son('".$num."_".$num2."_".$num3."_".$num4."_".$num5."_".$num6."')>";
																			echo $data6['sub6'];
																			echo "</td></tr>";
																		}
																		echo "<tr id=".$num."_".$num2."_".$num3."_".$num4."_".$num5."_".$num6."_diff style='display:none'><td class=diffvw>&nbsp;</td><td class=diffvw>&nbsp;</td><td class=diffvw>&nbsp;</td><td class=diffvw>&nbsp;</td><td class=diffvw>&nbsp;</td><td class=diffvw>&nbsp;</td><td class=diff_view>";
																		$geshi =& new GeSHi($data6['text'], "diff");
																		$diff_con = $geshi->parse_code(); 
																		echo $diff_con;
																		echo "</td></tr>";
																	}
																	//sub7_list
																	$sub7_list=array();
																	$num7=0;
																	$res7=mysql_query(
																			"select sub6,text from diff_".$now." where sub1='".$data['sub1'].
																			"' and sub2='".$data2['sub2'].
																			"' and sub3='".$data3['sub3'].
																			"' and sub4='".$data4['sub4'].
																			"' and sub5='".$data5['sub5'].
																			"' and sub6='".$data6['sub6'].
																			"'");
																	while($data7=mysql_fetch_array($res7)){
																		if(!in_array($data7['sub7'],$sub7_list)){
																			array_push($sub7_list,$data7['sub7']);
																			if($data7['sub7']){
																				$cnt_created=mysql_result(mysql_query(
																							"select count(*) from diff_".$now." where opt='created' and sub1='".$data['sub1'].
																							"' and sub2='".$data2['sub2'].
																							"' and sub3='".$data3['sub3'].
																							"' and sub4='".$data4['sub4'].
																							"' and sub5='".$data5['sub5'].
																							"' and sub6='".$data6['sub6'].
																							"' and sub7='".$data6['sub7']."'"
																							),0,0);
																				$cnt_deleted=mysql_result(mysql_query(
																							"select count(*) from diff_".$now." where opt='deleted' and sub1='".$data['sub1'].
																							"' and sub2='".$data2['sub2'].
																							"' and sub3='".$data3['sub3'].
																							"' and sub4='".$data4['sub4'].
																							"' and sub5='".$data5['sub5'].
																							"' and sub6='".$data6['sub6'].
																							"' and sub7='".$data6['sub7']."'"
																							),0,0);
																				$cnt_modified=mysql_result(mysql_query(
																							"select count(*) from diff_".$now." where opt='modified' and sub1='".$data['sub1'].
																							"' and sub2='".$data2['sub2'].
																							"' and sub3='".$data3['sub3'].
																							"' and sub4='".$data4['sub4'].
																							"' and sub5='".$data5['sub5'].
																							"' and sub6='".$data6['sub6'].
																							"' and sub7='".$data6['sub7']."'"
																							),0,0);
																				echo "\t\t\t\t\t\t<tr id=".$num."_".$num2."_".$num3."_".$num4."_".$num5."_".$num6."_".++$num7."  style='display:none'><td class=diffvw>&nbsp;</td><td class=diffvw>&nbsp;</td><td class=diffvw>&nbsp;</td><td class=diffvw>&nbsp;</td><td class=diffvw>&nbsp;</td><td class=diffvw>&nbsp;</td><td>";
																				$subed7=mysql_result(mysql_query("select count(*) from diff_".$now." where sub1='".$data['sub1']."' and sub2='".$data2['sub2']."' and sub3='".$data3['sub3']."' and sub4='".$data4['sub4']."' and sub5='".$data5['sub5']."' and	sub6='".$data6['sub6']."' and sub7='".$data7['sub7']."'"),0,0);
																				if($cnt_modified){
																					if($subed7<=1)
																						echo"<p style='color:green;cursor:pointer' onclick=view_diff('".$num."_".$num2."_".$num3."_".$num4."_".$num5."_".$num6."_".$num7."_diff')>";
																					else
																						echo"<p style='color:green;cursor:pointer' onclick=call_son('".$num7."')>";
																					echo $data7['sub7'];
																					echo "</td></tr>";
																				}else if($cnt_created){
																					if($subed7<=1)
																						echo"<p style='color:blue;cursor:pointer' onclick=view_diff('".$num."_".$num2."_".$num3."_".$num4."_".$num5."_".$num6."_".$num7."_diff')>";
																					else
																						echo"<p style='color:blue;cursor:pointer' onclick=call_son('".$num7."')>";
																					echo $data7['sub7'];
																					echo "</td></tr>";
																				}else if($cnt_deleted){
																					if($subed7<=1)
																						echo"<p style='color:red;cursor:pointer' onclick=view_diff('".$num."_".$num2."_".$num3."_".$num4."_".$num5."_".$num6."_".$num7."_diff')>";
																					else
																						echo"<p style='color:red;cursor:pointer' onclick=call_son('".$num7."')>";
																					echo $data7['sub7'];
																					echo "</td></tr>";
																				}
																				echo "<tr id=".$num."_".$num2."_".$num3."_".$num4."_".$num5."_".$num6."_".$num7."_diff style='display:none'><td class=diffvw>&nbsp;</td><td class=diffvw>&nbsp;</td><td class=diffvw>&nbsp;</td><td class=diffvw>&nbsp;</td><td class=diffvw>&nbsp;</td><td class=diffvw>&nbsp;</td><td class=diffvw>&nbsp;</td><td class=diff_view>";
																				$geshi =& new GeSHi($data7['text'], "diff");
																				$diff_con = $geshi->parse_code(); 
																				echo $diff_con;
																				echo "</td></tr>";
																			}
																		}
																	}
																}
															}
														}
													}
												}
											}
										}
									}
								}
							}
								
						}
					}
		echo"</table>";
				}
				
?>				</div><!--end of maincontents1_center_diff_result-->
					</td>
				</tr>
		</table>

<?

mysql_query("drop table `diff_".$now."`");
dbclose();
		
include"footer.php"; 

?>
