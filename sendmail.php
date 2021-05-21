<?

include"header.php";

dbconnect();

$query = "SELECT * FROM `ChangeLog` WHERE `commit` = '".$_GET['commit']."'";
$result = mysql_query($query);
while ($data = mysql_fetch_array($result)) {
	$content="\n\n\n\n\n-[Commit Contents]---------------------------\n\n".$data['content'];
}

dbclose();

?>
	<div>
		<h2><a href="<?=$_SERVER['PHP_SELF']?>">Mail to Author</a></h2>
	</div>	

	<form action="sendmail_ok.php" method=post>
		<div id="mailform">
			<div>
				<h3>Subject	</h3>
				<input type=text name=subject size=60 class="mail_subject2">
			</div>
			<div>
				<textarea name=memo cols=70 rows=20 class='mail_memo2'><?=$content?>
				</textarea>
				<input type=hidden name=email value=<?=$_GET['mail']?>>
				<input type=hidden name=commit value=<?=$_GET['commit']?>>
			</div>
			<div>
				<input type=submit value="Send" class="mail_send" >
			</div>
		</div>
	</form>

<?

include "footer.php";

?>
