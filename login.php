<?
include "header.php";

?>
<h2><a href="<?=$_SERVER['PHP_SELF']?>">Login</a></h2>
	<form method=post action='./login_check.php' name=form style='display:inline'>
		<table border=0 width=250px align=center cellpadding=0 cellspacing=0>
			<tr>
				<td width=60px align=left>E-mail</td>
				<td>
					<input type=text name=email class='input_id' tabindex=0>
				</td>
			</tr>
			<tr>
				<td width=60px align=left>Password</td>
				<td>
					<input type=password name=password class='input_pw'>
				</td>
			</tr>
			<tr >
				<td colspan=2 align=center>
					<input type=submit value=submit>
				</td>
			</tr>
		</table>
	</form>
<?

include "footer.php";
?>
