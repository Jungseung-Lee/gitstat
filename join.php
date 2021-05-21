<? 
include "header.php";

?>
<script>
	
	function add_individual() {
		window.open('./join_add_file.php','Add_Individual','scrollbars=yes,width=600,height=500');
	}

	function idcheck() {
		if (document.joinform.email.value) {
			window.open("./dupeid.php?email="+document.joinform.email.value,'DuplicationCheck','scrollbars=yes,width=200,height=100');
			return;
	
		} else {
			alert("Please,enter your E-mail address");
			return;
		}
	}

	function Check_Send(joinform) {
	
		<? if(!loggedin()){?>

		mail = document.joinform.email.value;
		mailError = new String(document.joinform.email.value);
		a = mailError.indexOf('@');
		b = mailError.lastIndexOf('.');
		if ((mail== "") || (mail.indexOf('@') == -1) || (mail.indexOf('.') == -1) || (mailError.slice(b+1) == "met")) {
			alert("Please, enter correct E-mail address");
			return;
		}

		if(!document.joinform.password.value){
			alert("Please, enter your password");
			document.joinform.password.focus();
			return;
		}
		if(document.joinform.password.value.length < 4){
			alert("Password must be longer than 4 characters.");
			document.joinform.password.focus();
			return;
		}

		<?}?>	
		
		joinform.submit();
		return true;
	}

	var im=0;
	var im_file = 20;

	function add_category2(big,no,path) {
		var table = document.getElementById('cate_list');
		var tr=document.createElement("tr");
		tr.setAttribute("id","cate_list_"+im);
		var td=document.createElement("td");
		var td2=document.createElement("td");
		td.innerHTML=big+"  |  "+path;
		td2.innerHTML="<a onclick='del_category("+im+")' style='color:red;cursor:pointer;font-weight:bold'>Remove</a>";
		tr.appendChild(td);
		tr.appendChild(td2);
		table.appendChild(tr);
		var input=document.createElement("input");
		input.setAttribute("type","hidden");
		input.setAttribute("name","cate_list_"+im);
		input.setAttribute("id","cate_list_in_"+im);
		input.setAttribute("value",no);
		var dd = document.getElementById('chch');
		dd.appendChild(input);
		im++;
	}

	function add_category_file(big,no,path) {
		var table = document.getElementById('cate_list');
		var tr=document.createElement("tr");
		tr.setAttribute("id","cate_list_"+im_file);
		var td=document.createElement("td");
		var td2=document.createElement("td");
		td.innerHTML=big+"  |  "+path;
		td2.innerHTML="<a onclick='del_category("+im_file+")' style='color:red;cursor:pointer;font-weight:bold'>Remove</a>";
		tr.appendChild(td);
		tr.appendChild(td2);
		table.appendChild(tr);
		var input=document.createElement("input");
		input.setAttribute("type","hidden");
		input.setAttribute("name","cate_list_"+im_file);
		input.setAttribute("id","cate_list_in_"+im_file);
		input.setAttribute("value",no);
		var dd = document.getElementById('chch');
		dd.appendChild(input);
		im_file++;
	}
	
	function del_category(a) {
		var table = document.getElementById('cate_list');
		var tr=document.getElementById('cate_list_'+a);
		table.removeChild(tr);
		var input=document.getElementById('cate_list_in_'+a);
		var dd = document.getElementById('chch');
		dd.removeChild(input);	
	}

</script>
<form name=joinform id=joinform  <?if(loggedin()) echo"action='modify_ok.php'"; else echo"action='join_ok.php'"; ?>method=post enctype='multipart/form-data'>
<?

dbconnect();
if (!loggedin()) {
	?>
	<h2><a href="<?=$_SERVER['PHP_SELF']?>">Member Registration</a></h2>
	<?
} else {
	// user_id is only needed when logged in
	$user_id = getuser();
	?>
	<h2><a href="<?=$_SERVER['PHP_SELF']?>">Member Modify</a></h2>
	<?
}

?>
<table width=600 border=0 cellpadding=0 cellspacing=0 align=center>
	<tr>
		<td>
			<table width=640 border=0 cellpadding=0 cellspacing=0>
				<tr>
					<td width=100 height=25 style='letter-spacing:-1;color:555555;font-size:12px'><b>E-mail</b></td>
					<td width=540>
						<input type=text size=25 name=email style='border:1px solid #cccccc;color:555555;font-size:12px'<?
						if (loggedin()) {
							echo "value=$user_id readonly>";
						} else {
							echo "value=".$_POST['email'].">";
							echo "<input type=button class=input value='Duplication check' onclick='idcheck()'>";
						}
					?></td>
				</tr>
				<tr>
					<td height=25 style='letter-spacing:-1;color:555555;font-size:12px'><b>Password</b></td>
					<td><input type=password size=12 name=password style='border:1 solid cccccc;color:555555;font-size:12px;border:1px solid #cccccc'>
					 &nbsp;&nbsp;<?
						if (loggedin()) {
							echo "  Leave it blank, if you don't want to change your password.";
						}
					?></td>
				</tr>
				
				<tr>
					<td height=25 style='letter-spacing:-1;color:555555;font-size:12px;vertical-align:top'><b>Interesting Part</b></td>
					<td style='letter-spacing:-1;color:555555' id='chch'>
					<div>
					&nbsp;&nbsp;&nbsp;If you'll choose the Interesting Part, <u>we'll send you its latest changelog by E-mail.</u><br>
					&nbsp;&nbsp;&nbsp;Click on the interesting subcategories.
					Confirm your selection with 'OK' button.
					</div>
					</td>
				</tr>
				<tr>
					<td height=25 style='letter-spacing:-1;color:555555;font-size:12px'><b>Option</b></td>
					</td>
					<td><input type=checkbox name=emailhtml value=true<?
						if (loggedin()) {
							$emailhtml = mysql_result(mysql_query("SELECT `mailhtml` FROM `Members` WHERE `email` = '".$user_id."'"),0,0);

							if ($emailhtml == 1)
								echo " checked";
						}
					?>> Use HTML mail
					</td>
				</tr>
				<tr>
					<td></td><td>
					<input type=button class=input value='Track directory or files' onclick="add_individual()"><br>
					<table>
						<tbody id=cate_list></tbody>
					</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<br><br>
<table width=90% border=0 cellpadding=0 cellspacing=0 align=center>
	<tr>
		<td height=25 align=center><img src='./images/confirm_button.gif' onclick="Check_Send(joinform)" style='cursor:pointer'> 
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
</table>

<?
if (loggedin()) {
	$user_no = mysql_result(mysql_query("SELECT `no` FROM `Members` WHERE `email` = '".$user_id."'"),0,0);
	//echo $user_no;
	$result = mysql_query("SELECT * FROM `Memcategory` WHERE `user_no` = '".$user_no."'");
	while ($data = mysql_fetch_array($result)) {
		if ($data['subcategory1'] != 0) {
			$sub_name = mysql_result(mysql_query("SELECT `subcategory1` FROM `category1` WHERE `no` = ".$data['subcategory1']),0,0);
			if ($sub_name == 'arch') {
				$tt = "Architecture Dependent";
			} else if ($sub_name == 'Documentation' || $sub_name == 'scripts') {
				$tt = "Miscellaneous";
			} else {
				$tt = "Architecture Independent";
			}
			?>
			<script>add_category2("<?=$tt?>",<?=$data['subcategory1']?>,"/<?=$sub_name?>");</script>
			<?
		} else {
			?>
			<script>add_category_file("Individual directory or file","<?=$data['filename']?>","<?=$data['filename']?>");</script>
			<?
		}
	}
}

?>
</form>
<?

include "./footer.php";
?>
