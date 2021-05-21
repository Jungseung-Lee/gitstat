<?
	Header("Content-Type: application/vnd.ms-excel");
	Header("Content-Disposition: inline; filename=diffreport_".$_GET['file'].".xls"); 
	Header( "Content-Description: PHP5 Generated Data" );

	include "include/lib.php";

	$diffdir = getprojectconfig("DIFFDIR");

	dbconnect();
?>
<meta http-equiv='content-Type' content="text/html; charset=UTF-8">
<?
	function getitem($str){
		if(preg_match("/(.*)\/(.*)/",$str,$m)){
			return $m[1];
		}
	}

	$fcontents = fopen($diffdir."/".$_GET['file'],"r");
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
			//$temp=split("a/",$match[1]);
			//$data[$i][0]=$temp[1];
			$data[$i][0]=$match[1];
		}
		if(preg_match("/\+\+\+ (.*)/",$line,$match2)){
			//$temp2=split("b/",$match2[1]);
			//$data[$i][1]=$temp2[1];
			$data[$i][1]=$match2[1];
			$i++;
		}
	}
	
	$now=time();

	mysql_query("CREATE TABLE `diff_".$now."` (
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
	PRIMARY KEY  (`no`)
	)");


	for($m=0;$m<$i;$m++){
		if($data[$m][0]=="/dev/null"){
			preg_match("/b\/(.*)/",$data[$m][1],$temp);
			$ea=split("/",$temp[1]);
			$query="insert into diff_".$now."(opt,sub1,sub2,sub3,sub4,sub5,sub6,sub7,sub8,sub9) values('created',";
			for($l=0;$l<8;$l++){
				$query.="'".$ea[$l]."',";
			}
			$query.="'test')";
			mysql_query($query);
		}else if($data[$m][1]=="/dev/null"){
			preg_match("/a\/(.*)/",$data[$m][0],$temp);
			$ea=split("/",$temp[1]);
			$query="insert into diff_".$now."(opt,sub1,sub2,sub3,sub4,sub5,sub6,sub7,sub8,sub9) values('deleted',";
			for($l=0;$l<8;$l++){
				$query.="'".$ea[$l]."',";
			}
			$query.="'test')";
			mysql_query($query);
		}else{
			preg_match("/a\/(.*)/",$data[$m][0],$temp);
			$ea=split("/",$temp[1]);
			$query="insert into diff_".$now."(opt,sub1,sub2,sub3,sub4,sub5,sub6,sub7,sub8,sub9) values('modified',";
			for($l=0;$l<8;$l++){
				$query.="'".$ea[$l]."',";
			}
			$query.="'test')";
			mysql_query($query);
		}
		//echo $data[$m][0]." ".$data[$m][1]."<br>";
	}
?>
<table border=0 cellpadding=0 cellspacing=0 style='border:1px solid black'>
<tr><td>Legend : </td><td style='color:green'>modified</td><td style='color:blue'>created</td><td style='color:red'>deleted</td></tr>
</table>
<table border=0 cellpadding=0 cellspacing=0 style='border:1px solid black'>
<?	// sub1 list
	$sub1_list=array();
	//echo("select sub1 from diff_".$now."");
	$res=mysql_query("select sub1 from diff_".$now);
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
			echo "<tr><td style='border-left:1px solid black;border-bottom:1px solid black'>";
			if($cnt_modified){
				echo"<p style='color:green'>";
			echo $data['sub1'];
			echo "</td></tr>";
			}else if($cnt_created){
				echo"<p style='color:blue'>";
			echo $data['sub1'];
			echo "</td></tr>";
			}else if($cnt_deleted){
				echo"<p style='color:red'>";
			echo $data['sub1'];
			echo "</td></tr>";
			}}
			// sub2_list	
			$sub2_list=array();
			$res2=mysql_query("select sub2 from diff_".$now." where sub1='".$data['sub1']."'");
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
					echo "<tr><td></td><td style='border-left:1px solid black;border-bottom:1px solid black'>";
					if($cnt_modified){
						echo"<p style='color:green'>";
					echo $data2['sub2'];
					echo "</td></tr>";
					}else if($cnt_created){
						echo"<p style='color:blue'>";
					echo $data2['sub2'];
					echo "</td></tr>";
					}else if($cnt_deleted){
						echo"<p style='color:red>";
					echo $data2['sub2'];
					echo "</td></tr>";
					}}
					// sub3_list
						$sub3_list=array();
						$res3=mysql_query("select sub3 from diff_".$now." where sub1='".$data['sub1']."' and sub2='".$data2['sub2']."'");
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
								echo "<tr><td></td><td></td><td style='border-left:1px solid black;border-bottom:1px solid black'>";
								if($cnt_modified){
									echo"<p style='color:green'>";
								echo $data3['sub3'];
								echo "</td></tr>";
								}else if($cnt_created){
									echo"<p style='color:blue'>";
								echo $data3['sub3'];
								echo "</td></tr>";
								}else if($cnt_deleted){
									echo"<p style='color:red'>";
								echo $data3['sub3'];
								echo "</td></tr>";
								}}
								// sub4_list
								$sub4_list=array();
								$res4=mysql_query(
									"select sub4 from diff_".$now." where sub1='".$data['sub1'].
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
										echo "<tr><td></td><td></td><td></td><td style='border-left:1px solid black;border-bottom:1px solid black'>";
										if($cnt_modified){
											echo"<p style='color:green'>";
										echo $data4['sub4'];
										echo "</td></tr>";
										}else if($cnt_created){
											echo"<p style='color:blue'>";
										echo $data4['sub4'];
										echo "</td></tr>";
										}else if($cnt_deleted){
											echo"<p style='color:red'>";
										echo $data4['sub4'];
										echo "</td></tr>";
										}}
										//sub5_list
										$sub5_list=array();
										$res5=mysql_query(
												"select sub5 from diff_".$now." where sub1='".$data['sub1'].
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
												echo "<tr><td></td><td></td><td></td><td></td><td style='border-left:1px solid black;border-bottom:1px solid black'>";
												if($cnt_modified){
													echo"<p style='color:green'>";
												echo $data5['sub5'];
												echo "</td></tr>";
												}else if($cnt_created){
													echo"<p style='color:blue'>";
												echo $data5['sub5'];
												echo "</td></tr>";
												}else if($cnt_deleted){
													echo"<p style='color:red'>";
												echo $data5['sub5'];
												echo "</td></tr>";
												}}
												//sub6_list
												$sub6_list=array();
												$res6=mysql_query(
														"select sub6 from diff_".$now." where sub1='".$data['sub1'].
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
													echo "<tr><td></td><td></td><td></td><td></td><td></td><td style='border-left:1px solid black;border-bottom:1px solid black;'>";
													if($cnt_modified){
														echo"<p style='color:green'>";
													echo $data6['sub6'];
													echo "</td></tr>";
													}else if($cnt_created){
														echo"<p style='color:blue'>";
													echo $data6['sub6'];
													echo "</td></tr>";
													}else if($cnt_deleted){
														echo"<p style='color:red'>";
													echo $data6['sub6'];
													echo "</td></tr>";
													}}
													//sub7_list
													$sub7_list=array();
													$res7=mysql_query(
														"select sub6 from diff_".$now." where sub1='".$data['sub1'].
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
														echo "<tr><td></td><td></td><td></td><td></td><td></td><td></td><td style='border-left:1px solid black;border-bottom:1px solid black;'>";
														if($cnt_modified){
															echo"<p style='color:green'>";
														echo $data7['sub7'];
														echo "</td></tr>";
														}else if($cnt_created){
															echo"<p style='color:blue'>";
														echo $data7['sub7'];
														echo "</td></tr>";
														}else if($cnt_deleted){
															echo"<p style='color:red'>";
														echo $data7['sub7'];
														echo "</td></tr>";
														}}
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
	}}}}
			
	mysql_query("drop table `diff_".$now."`");
?>
</table>
