<?
	session_start();

	//
	// Used to generate something that can be used as a session identifier. We have
	// to rely on some fixed parameters that can be retrieved at will. This also
	// means something that will not be changed during the lifetime of the session.
	// The db parameters are written to file and are a good candidate.
	//
	function getuniqueparam() {

		$dbparams = getdbconfig();

		return $dbparams['HOST']."_".$dbparams['DB'];
	}

	//
	// Function to retrieve the database specific configuration. This is probably a
	// temporary function. It's needed for some abstraction.
	// All MySQL related stuff should probably go to
	// libgather.php or libdb.php or .....
	//
	function getdbconfig() {

		include "./gstat_rw/config/dbconfig.php";

		$dbparams = array();
		$dbparams['HOST'] = $GSTAT[HOST] = localhost;
		$dbparams['ID'] = $GSTAT[ID];
		$dbparams['PASS'] = $GSTAT[PASS];
		$dbparams['DB'] = $GSTAT[DB];

		return $dbparams;
	}

	//
	// Function to retrieve project specific information.
	// FIXME: Is this the correct place?
	// In the future this information will be put
	// into the database. All MySQL related stuff should than probably go to
	// libgather.php or libdb.php or .....
	//
	function getprojectconfig($parameter) {

		include "./gstat_rw/config/gitstatconfig.php";

		$projectparams = array();

		// The version
		$projectparams['GITVER'] = "v0.5";

		// Gitstat tools config info
		$projectparams['GRAPHMODULEDIR'] = $GSTAT[GRAPHMODULEDIR];
		$projectparams['GESHIDIR'] = $GSTAT[GESHIDIR];

		// Gitstat directory config info
		$projectparams['RWDIR'] = $GSTAT[RWDIR];
		$projectparams['DIFFDIR'] = $GSTAT[RWDIR]."/diff";
		$projectparams['GITDIR'] = $GSTAT[GITDIR];
		$projectparams['GIT_PATH'] = "--git-dir=".$GSTAT[GITDIR]."/.git";

		// Gitstat url
		$projectparams['GITURL'] = $GSTAT[GITURL];

		// Git tree/project information
		$projectparams['GIT_TREE_DESC'] = $GSTAT[GIT_TREE_DESC];
		$projectparams['GIT_TREE_OWNER'] = $GSTAT[GIT_TREE_OWNER];
		$projectparams['GIT_TREE_URL'] = $GSTAT[GIT_TREE_URL];
		$projectparams['GIT_PROJECT_NAME'] = $GSTAT[GIT_PROJECT_NAME];

		$projectparams['GIT_EXE_PATH'] = $GSTAT[GIT_EXE_PATH];

		// Only return the parameter asked for
		if ($parameter)
			return $projectparams[$parameter];

		// Return the full array of parameters
		return $projectparams;
	}

	function loggedin() {

		$sessionid = getuniqueparam();

		if ($_SESSION[$sessionid])
			return true;
		else
			return false;
	}

	function getuser() {

		$sessionid = getuniqueparam();

		if (loggedin()) 
			return $_SESSION[$sessionid];
		else
			return false;
	}

	function checkprivilege() {

		if (loggedin()) {
			// Logged in, check the privilege
			dbconnect();
			$email = getuser();
			$result2 = mysql_query("SELECT * FROM `Members` WHERE `email` = '$email'");
			while ($data = mysql_fetch_array($result2)) {
				$level = $data[privilege];
			}
			dbclose();
		} else {
			$level = 5;
		}

		return $level;
	}

	function accountexists($checkemail) {

		$count = mysql_result(mysql_query("SELECT COUNT(*) FROM `Members` WHERE `email` = '$checkemail'"), 0, 0);
		return ($count == 1);
	}

	// Connect to the database. Should probably not be in this file
	function dbconnect() {

		$dbparams = getdbconfig();

		mysql_connect($dbparams['HOST'], $dbparams['ID'], $dbparams['PASS']);
		mysql_select_db($dbparams['DB']);
	}

	// Close the connection to the database. Again probably shouldn't be here. Only used
	// to abstract some MySQL stuff
	function dbclose() {

		mysql_close();
	}

	function err($comment,$backurl = " ") {

		echo"			
			<table width=100% height=300 cellpadding=0 cellspacing=0 border=0>
		    <tr>
			  <td align=center valign=middle>
			  <table cellpadding=0 cellspacing=0 border=0>
			  <tr>
				<td align=left height=30 ></td>
			  </tr>
			  <tr>
				<td align=center height=30 ><b>$comment</b></td>
			  </tr>
			 
		      </table>
			  </td>
		    </tr>
			</table>
		";
	}

	function moveto($url) {
		echo "<meta http-equiv='refresh' content='0;url=$url'>";
	}

	// FIXME: Should take font into consideration.
	function cut_str($string,$limit_length) {

		$string_length = strlen( $string );
		if ($limit_length < $string_length) {
			$string = substr( $string, 0, $limit_length )."..";
			$han_char = 0;
			for ( $i = $limit_length - 1; $i >= 0; $i-- ) {
				$lastword = ord( substr( $string, $i, 1 ) );
				if ( 127 > $lastword )
					break;
				else 
					$han_char++;
			}
			if ( $han_char%2 == 1 )
				$string = substr( $string, 0, $limit_length-1 ) . ".";
		}
		return $string;
	}

	function login($checkemail,$checkpw) {

		// We need something to check against
		if ($checkemail == '' || $checkpw == '')
			return false;

		dbconnect();

		// Is the email already a member
		$conn = mysql_query("select * from Members where email = '$checkemail'");
		if (mysql_num_rows($conn) == 0) {
			dbclose();
			return false;
		}

		// Let MySQL create a hash based on the given cleartext password
		$result = mysql_result(mysql_query("select password('$checkpw')"),0,0);

		while ($data = mysql_fetch_array($conn)) {
			$pw = $data['password'];
			if ($pw == $result) {
				$sessionid = getuniqueparam();

				$_SESSION[$sessionid]=$checkemail;

				// This makes sure we can have multiple gitstat sessions on 
				// one webserver.
				$_SESSION['sessioncount']++;
			} else {
				dbclose();
				return false;
			}
		}
		dbclose();
		return true;
	}

	function logout() {

		// We can't logout if we are not logged in
		if (!loggedin())
			return false;

		$_SESSION[$sessionid]=$checkemail;

		// 'logout' of this gitstat session
		unset($_SESSION[$sessionid]);

		// Decrease the sessioncount. Only if it's 0 we destroy the session
		// so not to 'logout' of other gitstat sessions on this webserver.
		$_SESSION['sessioncount']--;
		if ($_SESSION['sessioncount'] == 0) {
			unset($_SESSION['sessioncount']);
			session_destroy();
		}
			
		return true; 
	}

	function array_push_associative(&$arr) {
		$args = func_get_args();
		foreach ($args as $arg) {
			if (is_array($arg)) {
				foreach ($arg as $key => $value) {
					$arr[$key] = $value;
					$ret++;
				}
			} else {
				$arr[$arg] = "";
			}
		}
		return $ret;
	}
