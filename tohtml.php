<?
	Header("Content-Type: application/octet-stream");
	switch($_GET['search_opt']){
		case 1:
			$file="Date_".$_GET['year1'].$_GET['month1'].$_GET['day1']."_".
				  $_GET['year2'].$_GET['month2'].$_GET['day2'];
			break;
		case 2:
			$file="Subject_".$_GET['search2'];
			break;
		case 3:
			$file="Author_".$_GET['search3'];
			break;
		case 4:
			$file="Content_".$_GET['search4'];
			break;
		case 5:
			$file="Kernelversion_".$_GET['search5'];
			break;
	}
	Header("Content-Disposition: inline; filename=".$file.".htm"); 
	header( "Content-Description: PHP5 Generated Data" );
	
	include "include/lib.php";
	dbconnect();
?>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<style>
.underline{border-bottom:1px double black;}
.outline{border:1px double black;}
</style>
<table border=1 cellpadding=0 cellspacing=0 class=outline>
<tr>
	<td class=underline>Category</td>
	<td class=underline>Subject</td>
	<td class=underline>Content</td>
	<td class=underline>Committer</td>
	<td class=underline>Author</td>
	<td class=underline>Commitdate</td>
	<td class=underline>Authordate</td>
</tr>
<?
	switch($_GET['search_opt']){
		case 1: // search by date 
			$start_time=gmmktime(0,0,0,$_GET['month1'],$_GET['day1'],$_GET['year1']);
			$end_time=gmmktime(24,0,0,$_GET['month2'],$_GET['day2'],$_GET['year2']);
			$query="select * from ChangeLog where commitdate<=".
					$end_time.
					" and commitdate>=".
					$start_time.
					" order by commitdate desc";
			break;
		case 2: // 제목검색
			$query="select * from ChangeLog where subject like '%".
					$_GET['search2'].
					"%' order by commitdate desc";
			break;
		case 3: // 이름검색
			$query="select * from ChangeLog where author like '%".
					$_GET['search3'].
					"%' order by commitdate desc";
			break;
		case 4: // 내용검색
			$query="select * from ChangeLog where content like '%".
					$_GET['search4'].
					"%' order by commitdate desc";
			break;
		case 5: // 커널버젼검색
			$temp_q="select no from v_tag where name='".$_GET['search5']."'";
			$kv=mysql_result(mysql_query($temp_q),0,0);
			$query="select * from ChangeLog where version='".$kv.
					"' order by commitdate desc";
			break;
		default:
			$query="select * from ChangeLog order by commitdate desc";
			break;
	}
	$result=mysql_query($query);
	while($data=@mysql_fetch_array($result)){
?>
<tr>
	<td>
	<?
		$sub1=@mysql_result(mysql_query("select subcategory1 from Logcategory where commit='".$data['commit']."' limit 1"),0,0); 
		if(!$sub1) 
			$tt="unknown";
		else
			$tt=@mysql_result(mysql_query("select subcategory1 from category1 where no=".$sub1),0,0);
		echo $tt; 
	?>
	</td>
	<td><?=$data['subject']?></td>
	<td><?=$data['content']?></td>
	<td><?=$data['committer']?></td>
	<td><?=$data['author']?></td>
	<td><?=date("m/d",$data['commitdate'])?></td>
	<td><?=date("m/d",$data['authordate'])?></td>
</tr>
<?
	}
?>
</table>
