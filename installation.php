<?
include "header.php";
include "include/libdb.php";

// Check if the config directory exist and is writable
$configdir = "gstat_rw/config";
$dbdir_correct = false;
if (file_exists($configdir) && is_writable($configdir)) {
	$dbdir_correct = true;
}

// Check if we already have a dbconfig file
$dbconfig = "gstat_rw/config/dbconfig.php";
if (is_readable($dbconfig)) {
	include "$dbconfig";
}

function checkdb($host,$user,$pw,$db) {
	$connect = mysql_connect("$host","$user","$pw");
	if (!$connect)
		return false;
	if ($db != "") {
		$dbconnect = mysql_select_db("$db");
		if (!$dbconnect) {
			mysql_close($connect);
			return false;
		}
	}
	
	mysql_close($connect);
	return true;
}

// GET and POST variables
$part = $_GET['part'];

$servername = $_POST['servername'];
$username = $_POST['username'];
$password = $_POST['password'];
$dbname = $_POST['dbname'];
$adminname = $_POST['adminname'];
$adminpw = $_POST['adminpw'];

// If we already have a database settings file, check it's correctness
//
$dbfile_connect = false;
$dbfile_correct = false;
if (isset($GSTAT[HOST]) && isset($GSTAT[ID]) && isset($GSTAT[PASS]) && isset($GSTAT[DB])) {
	if (checkdb("$GSTAT[HOST]","$GSTAT[ID]","$GSTAT[PASS]","")) {
		$dbfile_connect = true;
		if (checkdb("$GSTAT[HOST]","$GSTAT[ID]","$GSTAT[PASS]","$GSTAT[DB]")) {
			$dbfile_correct = true;
		}
	}
}

// Check which button was pushed and act accordingly
// The currently defined buttons for the configuration part are:
//
// Step		Name			Text (title)
// ===================================================================
// 1		checkconnect		Check connection
// 2		checkdb			Check if the database exists
// 3		save			Save configuration
// 4		createdb		Create database
// 5		filldb			Fill the database
//
// And for the admin user part:
//
// Name			Text
// ===========================================
// createadmin		Create an admin user
//
$current_step = 0;

if ($_POST['checkconnect'] == true) {
	// Do we have the needed parameters ?
	if (!$servername || !$username || !$password) {
		$connect_status = "Fill in the needed (3) fields";
	} else {
		if (checkdb("$servername","$username","$password","")) {
			$current_step = 1;
		} else {
			$connect_status = "Cannot connect to the server with these parameters!!";
		}
	}
} elseif ($_POST['checkdb'] == true) {
	$current_step = 1;

	// Do we have the needed parameter ?
	if (!$dbname) {
		$dbcheck_status = "Fill in the needed field";
	} else {
		if (checkdb("$servername","$username","$password","$dbname")) {
			$dbcheck_status = "Database already exists";
		} else {
			$current_step = 2;
		}
	}
} elseif ($_POST['save'] == true) {
	$current_step = 2;

	// Write the settings to the database settings file
	$dbfile_status = "";
	$handle = fopen($dbconfig, "w");
	if ($handle) {
		fwrite($handle, "<?\n");
		fwrite($handle, "\$GSTAT[HOST] = $servername;\n");
		fwrite($handle, "\$GSTAT[ID] = $username;\n");
		fwrite($handle, "\$GSTAT[PASS] = $password;\n");
		fwrite($handle, "\$GSTAT[DB] = $dbname;\n");
		fwrite($handle, "?>\n");
		fclose($handle);
		$current_step = 3;
	} else {
		$dbfile_status = "Can't open '$dbconfig' for writing (check permissions)";
	}
} elseif ($_POST['createdb'] == true) {
	$current_step = 3;

	// Let's try and create the database
	$connect = mysql_connect("$GSTAT[HOST]","$GSTAT[ID]","$GSTAT[PASS]");
	$query = "CREATE DATABASE $dbname";
	if (mysql_query($query, $connect)) {
		$current_step = 4;
	} else {
		$dbcreate_status = "Error creating database: ".mysql_error();
	}
	mysql_close($connect);
} elseif ($_POST['filldb'] == true) {
	$current_step = 4;

	$connect = mysql_connect("$GSTAT[HOST]","$GSTAT[ID]","$GSTAT[PASS]");
	$dbconnect = mysql_select_db("$GSTAT[DB]", $connect);
	if (initdb()) {
		updatedb();
		$current_step = 5;
	} else {
		$dbfill_status = "Database filling failed: ".mysql_error();
	}
	mysql_close($connect);
} elseif ($_POST['createadmin'] == true) {
	if (!$adminname || !$adminpw) {
		$admincreate_status = "Fill in the needed (2) fields";
	} else {
		$connect = mysql_connect("$GSTAT[HOST]","$GSTAT[ID]","$GSTAT[PASS]");
		$dbconnect = mysql_select_db("$GSTAT[DB]", $connect);
		$query = "SELECT `email` FROM `Members` WHERE `email` = '$adminname'";
		$result = mysql_query($query, $connect);
		if (mysql_num_rows($result) > 0) {
			$admincreate_status = "Admin user '$adminname' already exists";
		} else {
			$query = "INSERT `Members` (`email`, `password`, `privilege`) VALUES ('$adminname', PASSWORD('$adminpw'), 1)";
			if (mysql_query($query, $connect)) {
				$admincreate_status = "Admin user was succesfully created";
			} else {
				$admincreate_status = "Admin user creation failed: ".mysql_error();
			}
		}
		mysql_close($connect);
	}
} else {
	// No button was pushed see if we have some (correct) info from the database settings file
	if ($dbfile_connect == true) {
		$servername = $GSTAT[HOST];
		$username = $GSTAT[ID];
		$password = $GSTAT[PASS];
	}
}

?>
<h2><a href="<?=$_SERVER['PHP_SELF']?>">Installation</a></h2>
<p>You have been redirected to this page as the installation is not done and/or finished. The main steps are:</p>
<ul>
	<li><a href="<?=$_SERVER['PHP_SELF']?>?part=1">Configure the database settings</a></li>
	<p>The settings are needed to be able to connect to the database server and database.
	The settings will be stored in a local file <?=$dbconfig;?>.</p><br>
	<li>Create the database</li>
	<p>The database will be created with the settings given in the previous step.</p><br>
	<li>Fill the initial database</li>
	<p>This will populate the database with the needed tables.</p><br>
	<li><a href="<?=$_SERVER['PHP_SELF']?>?part=2">Create the first gitstat admin user</a></li>
	<p>The admin user (this can eventually be more than one) is the only one that can manage gitstat after installation.
	This user (in contrast with users created via 'Join') doesn't need an email as it's username</p><br>
	<li>Remove the installation.php file (manual task).</li>
	<p>Not doing so will make sure you return to this page over and over again.</p><br>
	<li><a href="./login.php">Configure gitstat via the Admin menu</a></li>
	<p>After you've logged in as admin you should set some of the specifics for this gitstat instance. Like where one can find
	the git-repository or the description</p>
</ul>
<?

switch ($part) {
	case 1: //Database configuration
		?>
		<p class="date"><img src="images/timeicon.gif" alt="" /></p>
		<div>
		<?if ($current_step == 0) {
			?>
			<h2>Database Server Settings</h2>
			<p>
			Please specify the servername, the database user and password.
			</p><br>
		<?} elseif ($current_step == 1) {
			?>
			<h2>Database Settings</h2>
			<p>
			The connection details are correct. Please specify the database name.
			</p><br>
		<?} elseif ($current_step == 2) {
			?>
			<h2>Save Configuration</h2>
			<p>
			<?if ($dbdir_correct) {
				?>
				The connection settings are correct and the database doesn't exist.
				Press the button to save the below settings to <?=$dbconfig;?>
				<?
			} else {
				?>
				The config directory is either not there or can't be written to. Please correct this.
				<?
			}?>
			</p><br>
		<?} elseif ($current_step == 3) {
			?>
			<h2>Database Creation</h2>
			<p>
			The database will be created based on the settings below.
			</p><br>
		<?} elseif ($current_step == 4) {
			?>
			<h2>Database Filling</h2>
			<p>
			The database will be filled with the necessary tables.
			</p><br>
		<?} elseif ($current_step == 5) {
			?>
			<h2>Configuration is finished</h2>
			<p>
			The database configuration and creation part is done.
			Please proceed with <a href="<?=$_SERVER['PHP_SELF']?>?part=2">adding the first admin user</a>.
			</p><br>
		<?}?>
		<form name=dbsettings method=post action='<?=$_SERVER['PHP_SELF']?>?part=1' enctype='multipart/form-data'>
			<table border=0 cellpadding=0 cellspacing=0>
				<th>
					<tr>
						<td width=80px align=left></td>
					</tr>
					<tr>
						<td width=100px align=right></td>
					</tr>
				</th>
				<tr <?if ($current_step >= 1) echo "bgcolor='#C0C0C0'";?>>
					<td>Servername</td>
					<td><input type=text name=servername tabindex=1 value=<?=$servername?> <?if ($current_step >= 1) echo "readonly";?>></td>
				</tr>
				<tr <?if ($current_step >= 1) echo "bgcolor='#C0C0C0'";?>>
					<td>Database user</td>
					<td><input type=text name=username tabindex=2 value=<?=$username?> <?if ($current_step >= 1) echo "readonly";?>></td>
				</tr>
				<tr <?if ($current_step >= 1) echo "bgcolor='#C0C0C0'";?>>
					<td>Password</td>
					<td><input type=text name=password tabindex=3 value=<?=$password?> <?if ($current_step >= 1) echo "readonly";?>></td>
				</tr>
				<?if ($current_step == 0) {
					?>
					<tr>
						<td colspan=2 align=right><input type=submit value="Check connection"></td>
						<input type=hidden name=checkconnect value=true>
					</tr>
					<tr>
						<td width=180px colspan=2 align=right>
							<?if ($connect_status != "") echo "<font color=red><b>$connect_status</b></font>"; ?>
						</td>
					</tr>
				<?} elseif ($current_step >= 1) {
					?>
					<tr <?if ($current_step >= 2) echo "bgcolor='#C0C0C0'";?>>
						<td>Databasename</td>
						<td><input type=text name=dbname tabindex=4 value=<?=$dbname?> <?if ($current_step >= 2) echo "readonly";?>></td>
					</tr>
					<?if ($current_step == 1) {
						?>
						<tr>
							<td colspan=2 align=right><input type=submit value="Check database"></td>
							<input type=hidden name=checkdb value=true>
						</tr>
						<tr>
							<td width=180px colspan=2 align=right>
								<?if ($dbcheck_status != "") echo "<font color=red><b>$dbcheck_status</b></font>"; ?>
							</td>
						</tr>
					<?} elseif ($current_step == 2) {
						?>
						<?if ($dbdir_correct) {
							?>
							<tr>
								<td colspan=2 align=right><input type=submit value="Save configuration"></td>
								<input type=hidden name=save value=true>
							</tr>
							<?
						} else {
							?>
							<tr>
								<td colspan=2 align=right><input type=submit value="Retry"></td>
								<input type=hidden name=checkdb value=true>
							</tr>
							<?
						} ?>
						<tr>
							<td width=180px colspan=2 align=right>
								<?if ($dbfile_status != "") echo "<font color=red><b>$dbfile_status</b></font>"; ?>
							</td>
						</tr>
					<?} elseif ($current_step == 3) {
						?>
						<tr>
							<td colspan=2 align=right><input type=submit value="Create database"></td>
							<input type=hidden name=createdb value=true>
						</tr>
						<tr>
							<td width=180px colspan=2 align=right>
								<?if ($dbcreate_status != "") echo "<font color=red><b>$dbcreate_status</b></font>"; ?>
							</td>
						</tr>
					<?} elseif ($current_step == 4) {
						?>
						<tr>
							<td colspan=2 align=right><input type=submit value="Fill database"></td>
							<input type=hidden name=filldb value=true>
						</tr>
						<tr>
							<td width=180px colspan=2 align=right>
								<?if ($dbfill_status != "") echo "<font color=red><b>$dbfill_status</b></font>"; ?>
							</td>
						</tr>
					<?}
				}?>
			</table>
		</form>
		</div>
		<?
		break;

	case 2: //Create the first admin user
		?>
		<p class="date"><img src="images/timeicon.gif" alt="" /></p>
		<h2><a href="<?=$_SERVER['PHP_SELF']?>?part=2">Create admin user</a></h2>
		<div>
		<?if (!$dbfile_correct) {
			?>
			<p>You have to configure and create the database before you can add an admin user</p<br>
		<?} else {
			?>
			<p>
			Please specify a name for this administrator and a password
			</p><br>
			<form name=admincreate method=post action='<?=$_SERVER['PHP_SELF']?>?part=2' enctype='multipart/form-data'>
				<table border=0 cellpadding=0 cellspacing=0>
					<th>
						<tr>
							<td width=80px align=left></td>
						</tr>
						<tr>
							<td width=100px align=right></td>
						</tr>
					</th>
					<tr>
						<td>Admin name</td>
						<td><input type=text name=adminname tabindex=1 value=<?=$adminname?>></td>
					</tr>
					<tr>
						<td>Password</td>
						<td><input type=text name=adminpw tabindex=2 value=<?=$adminpw?>></td>
					</tr>
					<tr>
						<td colspan=2 align=right><input type=submit value="Create admin user"></td>
						<input type=hidden name=createadmin value=true>
					</tr>
					<tr>
						<td width=180px colspan=2 align=right><b><br><?=$admincreate_status?></b></td>
					</tr>
				</table>
			</form>
		<?}?>
		</div>
		<?
		break;
}

include "footer.php";
?>
