<?
include"header.php";

$projectname = getprojectconfig("GIT_PROJECT_NAME");

$indexsize = 40;
dbconnect();

	if($_GET['search']){
		switch($_GET['search_opt']){
			case 2:
				$_GET['year1']="";
				$_GET['month1']="";
				$_GET['day1']="";
				$_GET['year2']="";
				$_GET['month2']="";
				$_GET['day2']="";
				$_GET['search2']=$_GET['search'];
				break;
			case 3:
				$_GET['year1']="";
				$_GET['month1']="";
				$_GET['day1']="";
				$_GET['year2']="";
				$_GET['month2']="";
				$_GET['day2']="";
				$_GET['search3']=$_GET['search'];
				break;
			case 4:
				$_GET['year1']="";
				$_GET['month1']="";
				$_GET['day1']="";
				$_GET['year2']="";
				$_GET['month2']="";
				$_GET['day2']="";
				$_GET['search4']=$_GET['search'];
			case 5:
				$_GET['year1']="";
				$_GET['month1']="";
				$_GET['day1']="";
				$_GET['year2']="";
				$_GET['month2']="";
				$_GET['day2']="";
				$_GET['search5']=$_GET['search'];
				break;
		}
	}
	$s_flag="&search_opt=".$_GET['search_opt'].
		"&search2=".$_GET['search2'].
		"&search3=".$_GET['search3'].
		"&search4=".$_GET['search4'].
		"&search5=".$_GET['search5'].
		"&year1=".$_GET['year1'].
		"&month1=".$_GET['month1'].
		"&day1=".$_GET['day1'].
		"&year2=".$_GET['year2'].
		"&month2=".$_GET['month2'].
		"&day2=".$_GET['day2'];
	?>
		
	<script>
	var data = new Array();
	var data2 = new Array();
	var data3 = new Array();
	var data4 = new Array();

	function select_option(value){
		var opt1=document.getElementById('search_option_1');
		var opt2=document.getElementById('search_option_2');
		var opt3=document.getElementById('search_option_3');
		var opt4=document.getElementById('search_option_4');
		var opt5=document.getElementById('search_option_5');
		var month1=document.getElementById('month1');
		var month2=document.getElementById('month2');
		var day1=document.getElementById('day1');
		var day2=document.getElementById('day2');
		if(value==1){
			opt1.style.display='block';opt2.style.display='none';opt3.style.display='none';opt4.style.display='none';opt5.style.display='none';
		}else if(value==2){
			opt1.style.display='none';opt2.style.display='block';opt3.style.display='none';opt4.style.display='none';opt5.style.display='none';
		}else if(value==3){
			opt1.style.display='none';opt2.style.display='none';opt3.style.display='block';opt4.style.display='none';opt5.style.display='none';
		}else if(value==4){
			opt1.style.display='none';opt2.style.display='none';opt3.style.display='none';opt4.style.display='block';opt5.style.display='none';
		}else if(value==5){
			opt1.style.display='none';opt2.style.display='none';opt3.style.display='none';opt4.style.display='none';opt5.style.display='block';
		}else{	
			opt1.style.display='block';opt2.style.display='none';opt3.style.display='none';opt4.style.display='none';opt5.style.display='none';
		}
	}


	function view_month1(){
		var today=new Date();
		var year1=document.getElementById('year1');
		var month1=document.getElementById('month1');
		var i;	

		for(j=0;j<12;j++)
		{
			if(data[j])
				month1.removeChild(data[j]);
		}

		data = new Array();

		if(year1.value==(1900+today.getYear()) || year1.value==today.getYear()){
			for(i=0;i<(today.getMonth()+1);i++){
				data[i]=document.createElement("option");
				data[i].setAttribute("value",i+1);
				data[i].innerHTML=i+1;
				month1.appendChild(data[i]);
				data[i].selected="selected";
			}
		}else{
			for(i=0;i<12;i++){
				data[i]=document.createElement("option");
				data[i].setAttribute("value",i+1);
				data[i].innerHTML=i+1;
				month1.appendChild(data[i]);
			}
		}

		<?
			if($_GET['month1']){
				?>
					month1.selectedIndex=<?=$_GET['month1']-1?>;
				<?
			}
		?>

	}

	function cday(year)
	{
		
		if ((year % 4) == 0) { 
			if ((year % 100) == 0) { 
				if ((year % 400) == 0) { 
					daynum = 29;
				}
				else {
					daynum = 28;
				}
			}
			else { 
				daynum = 29;
			}
		}
		else {
			daynum = 28;
		}
		return daynum;
	}

	function view_day1(){
		var today=new Date();
		var year1=document.getElementById('year1');
		var month1=document.getElementById('month1');
		var day1=document.getElementById('day1');
		var i,max_day;

		if((month1.value-1)%2==1){
			if(month1.value-1==1){
				max_day=cday(year1.value);
			}else{
				max_day=30;
			}
		}else{
			max_day=31;
		}

		for(j=0;j<31;j++)
		{
			if(data3[j])
				day1.removeChild(data3[j]);
		}

		data3 = new Array();

		if((year1.value==(1900+today.getYear()) || year1.value==today.getYear()) && month1.value-1==today.getMonth()){
			for(i=0;i<(today.getDate());i++){
				data3[i]=document.createElement("option");
				data3[i].setAttribute("value",i+1);
				data3[i].innerHTML=i+1;
				day1.appendChild(data3[i]);
				data3[i].selected="selected";
			}
		}else{
			for(i=0;i<max_day;i++){
				data3[i]=document.createElement("option");
				data3[i].setAttribute("value",i+1);
				data3[i].innerHTML=i+1;
				day1.appendChild(data3[i]);
			}
		}

		<?
			if($_GET['day1']){
				?>
					day1.selectedIndex=<?=$_GET['day1']-1?>;
				<?
			}
		?>
	}

	function view_month2(){
		var today=new Date();
		var year1=document.getElementById('year2');
		var month1=document.getElementById('month2');
		var i;	

		for(j=0;j<data2.length;j++)
		{
			if(data2[j])
				month1.removeChild(data[j]);
		}

		data2 = new Array();

		if(year1.value==(1900+today.getYear()) || year1.value==today.getYear()){
			for(i=0;i<(today.getMonth()+1);i++){
				data2[i]=document.createElement("option");
				data2[i].setAttribute("value",i+1);
				data2[i].innerHTML=i+1;
				month1.appendChild(data2[i]);
				data2[i].selected="selected";
			}
		}else{
			for(i=0;i<12;i++){
				data2[i]=document.createElement("option");
				data2[i].setAttribute("value",i+1);
				data2[i].innerHTML=i+1;
				month1.appendChild(data2[i]);
			}
		}
		<?
			if($_GET['month2']){
				?>
					month1.selectedIndex=<?=$_GET['month2']-1?>;
				<?
			}
		?>

	}

	function view_day2(){
		var today=new Date();
		var year1=document.getElementById('year2');
		var month1=document.getElementById('month2');
		var day1=document.getElementById('day2');
		var i,max_day;

		if((month1.value-1)%2==1){
			if(month1.value-1==1){
				max_day=cday(year1.value);
			}else{
				max_day=30;
			}
		}else{
			max_day=31;
		}

		for(j=0;j<31;j++)
		{
			if(data4[j])
				day1.removeChild(data4[j]);
		}

		data4 = new Array();

		if((year1.value==(1900+today.getYear()) || year1.value==today.getYear()) && month1.value-1==today.getMonth()){
			for(i=0;i<(today.getDate());i++){
				data4[i]=document.createElement("option");
				data4[i].setAttribute("value",i+1);
				data4[i].innerHTML=i+1;
				day1.appendChild(data4[i]);
				data4[i].selected="selected";
			}
		}else{
			for(i=0;i<max_day;i++){
				data4[i]=document.createElement("option");
				data4[i].setAttribute("value",i+1);
				data4[i].innerHTML=i+1;
				day1.appendChild(data4[i]);
			}
		}
		<?
			if($_GET['day2']){
				?>
					day1.selectedIndex=<?=$_GET['day2']-1?>;
				<?
			}
		?>
	}
	</script>
	

		<div>
			<h2><a href="<?=$_SERVER['PHP_SELF']?>">ChangeLog</a></h2>
		</div>	
		<div id="shortlog">	
		<table width=100% height=100px border=0 cellpadding=0 cellspacing=0>
			<tr>
				<td>
					<form action="<?=$_SERVER['PHP_SELF']?>" method=GET>
					<div id='changelog_search'>
						<div id='search_option'>
							<input type=radio name=search_opt value=1 id=opt1 <?if($_GET['search_opt']==1 || $_GET['search_opt']==NULL) echo "checked";?> onclick="select_option(1)">Date &nbsp;&nbsp;&nbsp;
							<input type=radio name=search_opt value=2 id=opt2 <?if($_GET['search_opt']==2) echo "checked";?> onclick="select_option(2)">Subject &nbsp;&nbsp;&nbsp;
							<input type=radio name=search_opt value=3 id=opt3 <?if($_GET['search_opt']==3) echo "checked";?> onclick="select_option(3)">Name or E-mail &nbsp;&nbsp;&nbsp;
							<input type=radio name=search_opt value=4 id=opt4 <?if($_GET['search_opt']==4) echo "checked";?> onclick="select_option(4)">Contents &nbsp;&nbsp;&nbsp;
							<input type=radio name=search_opt value=5 id=opt5 <?if($_GET['search_opt']==5) echo "checked";?> onclick="select_option(5)"><?=$projectname?> Ver. 
						</div>
						<div id='search_option_1'>
							Date : 
							<select name=year1 onchange="view_month1()" id='year1'>
								<?
								global $recent_year,$oldest_year;
								$recent_date=mysql_result(mysql_query("SELECT MAX(`commitdate`) FROM `ChangeLog`"),0,0);
								$oldest_date=mysql_result(mysql_query("SELECT MIN(`commitdate`) FROM `ChangeLog`"),0,0);
								$recent_year=date("Y",$recent_date);
								$oldest_year=date("Y",$oldest_date);
					
								for($i=$recent_year;$i>=$oldest_year;$i--){
									?>
										<option value=<?=$i?>
										<? 
										if($_GET['year1']==$i){ echo "selected=selected"; }
									?>
										><?=$i?></option>
										<?
								}
								?>
							</select>Y 
							<select name=month1 id='month1' onchange="view_day1();">
							</select>M
							<select name=day1 id='day1'>
							</select>D	- 
							<select name=year2 onchange="view_month2()" id='year2'>
							<?
							for($i=$recent_year;$i>=$oldest_year;$i--){
								?>
									<option value=<?=$i?>
									<? 
									if($_GET['year2']==$i){ echo "selected=selected"; }
								?>
									><?=$i?></option>
									<?
							}
						?>
							</select>Y
							<select name=month2 id='month2' onchange="view_day2();">
							</select>M
							<select name=day2 id='day2'>
							</select>D
						</div>
						<div id='search_option_2'>
							Subject : <input type=text size=20 name=search2 id=search2>
						</div>
						<div id='search_option_3'>
							Name or E-mail : <input type=text size=20 name=search3 id=search3>
						</div>
						<div id='search_option_4'>
							Contents : <input type=text size=20 name=search4 id=search4>
						</div>
						<div id='search_option_5'>
							<?=$projectname?> Ver. :
							<select name=search5>
							<?
							?>
							<option value="current">current</option>
							<?
							$result=mysql_query("select name from v_tag order by epoch desc");
							while($data=mysql_fetch_array($result)){
								$buffer = $data['name'];
						
								if(!empty($buffer) || $buffer!=$_GET['search5'])
								{
									if($_GET['search5']==$buffer)
										$select_s1="selected=\"selected\"";
									else
										$select_s1=" ";
									echo"<option value=\"$buffer\" $select_s1>$buffer</option>";
								}
							}
							?>
							</select>
							Subject : <input type=text size=10 name=search6 id=search6 value=<?=$_GET['search6']?>>&nbsp;&nbsp;&nbsp;
							Contents : <input type=text size=10 name=search7 id=search7 value=<?=$_GET['search7']?>>
						 </div>
					</div>
					<p style="padding-left:10px;">
					<input type=hidden name=submit value=1>
					<input type=submit value=Search>  
					<!-- <input type=button value='Summery Report' onclick="location.href='toshortreport.php'">
					--><? if($_GET['submit']){?>
					  <input type=button value='Save as HTML' onclick="location.href='tohtml.php?t=1<?=$s_flag?>'">
					<?}?>
					  
					  </p>
					  <input type=hidden name=page value=<?=$_GET['page']?>>
					</form>
				</td>
			</tr>
		</table>
		<table width=100% border=0 cellpadding=0 cellspacing=0>
			<tr>
				<td height=600px valign=TOP>
					<div id=shortlogcontents>
					<table border=0 cellpadding=0 cellspacing=0 width=100%>		
						<tr class="changelog_list_subject">
						<!--<td class="changelog_list_item_scate">Category</td>-->
							<td align=center><b>Date</b></td>
							<td align=center><b>Subject</b></td>
							<td align=left><b>Author</b></td>	  
						</tr>
								  <?
								  if(!$_GET['page']) $page=1;
								  else $page=$_GET['page'];
						$start=($page-1)*$indexsize;
						$last=$indexsize;
						switch($_GET['search_opt']){
						  case 1: 
							  $start_time=gmmktime(0,0,0,$_GET['month1'],$_GET['day1'],$_GET['year1']);
							  $end_time=gmmktime(24,0,0,$_GET['month2'],$_GET['day2'],$_GET['year2']);
							  $search_query="select count(*) from ChangeLog where commitdate<=".
								  $end_time.
								  " and commitdate>=".
								  $start_time.
								  "";
							  $query="select * from ChangeLog where commitdate<=".
								  $end_time.
								  " and commitdate>=".
								  $start_time.
								  " order by commitdate desc limit $start,$last";
							  break;
						  case 2:
							  $search_query="select count(*) from ChangeLog where subject like '%".
								  $_GET['search2'].
								  "%'";
							  $query="select * from ChangeLog where subject like '%".
								  $_GET['search2'].
								  "%' order by commitdate desc limit $start,$last";
							  $ajax_search=$_GET['search2'];
							  break;
						  case 3: 
							  $search_query="select count(*) from ChangeLog where author like '%".
								  $_GET['search3'].
								  "%'";
							  $query="select * from ChangeLog where author like '%".
								  $_GET['search3'].
								  "%' order by commitdate desc limit $start,$last";
							  $ajax_search=$_GET['search3'];
							  break;
						  case 4: 
							  $search_query="select count(*) from ChangeLog where content like '%".
								  $_GET['search4'].
								  "%'";
							  $query="select * from ChangeLog where content like '%".
								  $_GET['search4'].
								  "%' order by commitdate desc limit $start,$last";
							  $ajax_search=$_GET['search4'];
							  break;
						  case 5: 
							  if($_GET['search5']=="current"){
								  $temp_q="select max(no) from v_tag";
								  $kv=mysql_result(mysql_query($temp_q),0,0);				
								  $kv++;
							  }else{
								  $temp_q="select no from v_tag where name='".$_GET['search5']."'";
								  $kv=mysql_result(mysql_query($temp_q),0,0);
							  }
							  $search_query="select count(*) from ChangeLog where version='".$kv.
								  "' and subject like '%".$_GET['search6'].
								  "%' and content like '%".$_GET['search7']."%'";
							  $query="select * from ChangeLog where version='".$kv.
								  "' and subject like '%".$_GET['search6'].
								  "%' and content like '%".$_GET['search7'].
								  "%' order by commitdate desc limit $start,$last";
							  break;
						  default:
							  $search_query="select count(*) from ChangeLog";
							  $query="select * from ChangeLog order by commitdate desc, authordate desc limit $start,$last";
							  break;
						}
						
						$total=mysql_result(mysql_query($search_query),0,0);
						if($total>0){
						
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
								<table width=100% border=0 cellpadding=0 cellspacing=0>
									<tr>
										<td height=720px colspan=3 align=center style='font-size:20px'><b>No result</b>
										</td>
									</tr>
								</table>
							<?
						}
						?>
						  <tr class="changelog_list_bottom">
						  <td colspan=2>
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
						  <a href='./changelog-find.php?page=1<?=$s_flag?>'>
						  <img src='./images/forworddot.gif' alt='' style='border:0'>
						  </a>
						  <?   
						  if($page>1){
							  $page10m=$page-1;
							  ?>
								  <a href='./changelog-find.php?page=<?=$page10m?><?=$s_flag?>'>
								  [Previous]
								  </a>
								  <?
						  }
						if($page>10){
						  $page10m=$page-10;
						  ?>
							  <a href='./changelog-find.php?page=<?=$page10m?><?=$s_flag?>'>
							  [-10]
							  </a>
							  <?
						}            
						for($i=$pagef;$i<=$pagel;$i++){
						  ?>
							  <a href='./changelog-find.php?page=<?=$i?><?=$s_flag?>'>
							  <?if($page==$i){ echo"<font size=4 color=blue><b>".$i."</b></font>";}else echo"$i";?>
							  </a>
							  <?
						}
						if($pages-$page>10){
						  $page10p=$page+10;
						  ?>
							  <a href='./changelog-find.php?page=<?=$page10p?><?=$s_flag?>'>
							  [+10]
							  </a>
							  <?
						}
						if($pages-$page>0){
						  $page10p=$page+1;
						  ?>
							  <a href='./changelog-find.php?page=<?=$page10p?><?=$s_flag?>'>
							  [Next]
							  </a>
							  <?
						}
						?>
						  <a href='./changelog-find.php?page=<?=$pages?><?=$s_flag?>'>
						  <img src='./images/ffdot.gif' alt='' style='border:0'>
						  </a>
							</td>
						</tr>
					</table>
					</div>
				</td>
			</tr>
		</table>
		
			  <script>
			  <?
			  switch($_GET['search_opt']){
				  case 1:
					  echo" 
						  var opt = document.getElementById('opt1');
					  opt.checked=\"checked\";				
						  select_option(1);
					  ";
					  break;
				  case 2:
					  echo" 
						  var opt = document.getElementById('opt2');
					  var ss = document.getElementById('search2');
					  opt.checked=\"checked\";				
						  select_option(2);
					  ss.value=\"".$_GET['search2']."\";
					  ";
					  break;
				  case 3:
					  echo" 
						  var opt = document.getElementById('opt3');
					  var ss = document.getElementById('search3');
					  opt.checked=\"checked\";				
						  select_option(3);
					  ss.value=\"".$_GET['search3']."\";
					  ";
					  break;
				  case 4:
					  echo" 
						  var opt = document.getElementById('opt4');
					  var ss = document.getElementById('search4');
					  opt.checked=\"checked\";				
						  select_option(4);
					  ss.value=\"".$_GET['search4']."\";
					  ";
					  break;
				  case 5:
					  echo" 
						  var opt = document.getElementById('opt5');
					  opt.checked=\"checked\";				
						  select_option(5);
					  ";
					  break;
			  }
		  ?>
			  </script>

<script> view_month1(); view_day1(); view_month2(); view_day2();select_option(<?=$_GET['search_opt']?>);</script>
<?
include"footer.php";
?>

