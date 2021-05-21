<?

//
// Initializes the database with the basic schema. This is the schema
// as it was for the first version of gitstat. All other additions will
// be done via the updatedb() function.
//
function initdb() {

	$queries = array(
		"CREATE TABLE `ChangeLog` (
			`no` int(10) unsigned NOT NULL auto_increment,
			`commit` varchar(40) NOT NULL default '',
			`subject` varchar(90) NOT NULL default '',
			`content` text,
			`committer` varchar(70) NOT NULL default '',
			`author` varchar(70) NOT NULL default '',
			`commitdate` int(10) unsigned NOT NULL default '0',
			`authordate` int(10) unsigned NOT NULL default '0',
			`parents` varchar(200) NOT NULL default '',
			`tree` varchar(120) NOT NULL default '',
			`path` text,
			`version` int(10) unsigned NOT NULL default '0',
			PRIMARY KEY  (`no`),
			KEY `commit` (`commit`),
			KEY `commitdate` (`commitdate`),
			KEY `committer` (`committer`),
			KEY `author` (`author`),
			KEY `version` (`version`)
		)",
		"CREATE TABLE `Logcategory` (
			`no` int(10) unsigned NOT NULL auto_increment,
			`commit` varchar(40) NOT NULL default '',
			`Subcategory1` int(10) unsigned NOT NULL default '0',
			`subcategory2` int(10) unsigned NOT NULL default '0',
			`subcategory3` int(10) unsigned NOT NULL default '0',
			`subcategory4` int(10) unsigned NOT NULL default '0',
			`version` varchar(255) NOT NULL default '',
			PRIMARY KEY  (`no`),
			KEY `commit` (`commit`),
			KEY `subcategory1` (`subcategory1`),
			KEY `version` (`version`),
			KEY `subcategory2` (`subcategory2`)
		)",
		"CREATE TABLE `Members` (
			`no` int(10) unsigned NOT NULL auto_increment,
			`email` varchar(30) NOT NULL default '',
			`password` varchar(50) NOT NULL default '',
			`privilege` int(10) unsigned NOT NULL default '5',
			`mailhtml` int(10) unsigned NOT NULL default '0',
 			PRIMARY KEY  (`no`)
		)",
		"CREATE TABLE `Memcategory` (
			`no` int(10) unsigned NOT NULL auto_increment,
			`user_no` int(10) unsigned NOT NULL default '0',
			`subcategory1` int(10) unsigned NOT NULL default '0',
			`subcategory2` int(10) unsigned NOT NULL default '0',
			`subcategory3` int(10) unsigned NOT NULL default '0',
			`subcategory4` int(10) unsigned NOT NULL default '0',
			PRIMARY KEY  (`no`),
			KEY `user_no` (`user_no`),
			KEY `subcategory1` (`subcategory1`)
		)",
		"CREATE TABLE `category1` (
			`no` int(10) unsigned NOT NULL auto_increment,
			`subcategory1` varchar(30) NOT NULL default '',
			`description` text,
			PRIMARY KEY  (`no`)
		)",
		"CREATE TABLE `category2` (
			`no` int(10) unsigned NOT NULL auto_increment,
			`subcategory2` varchar(30) NOT NULL default '',
			`description` text,
			PRIMARY KEY  (`no`)
		)",
		"CREATE TABLE `category3` (
			`no` int(10) unsigned NOT NULL auto_increment,
			`subcategory3` varchar(30) NOT NULL default '',
			`description` text,
			PRIMARY KEY  (`no`)
		)",
		"CREATE TABLE `category4` (
			`no` int(10) unsigned NOT NULL auto_increment,
			`subcategory4` varchar(30) NOT NULL default '',
			`description` text,
			PRIMARY KEY  (`no`)
		)",
		"CREATE TABLE `diffmanager` (
			`no` int(50) NOT NULL auto_increment,
			`diff_filename` varchar(255) NOT NULL default '',
			`diff_filesize` bigint(255) NOT NULL default '0',
			`count` int(50) NOT NULL default '0',
			PRIMARY KEY  (`no`)
		)",
		"CREATE TABLE `mail_count` (
			`no` int(10) unsigned NOT NULL auto_increment,
			`department` int(10) unsigned NOT NULL default '0',
			`subcategory1` int(10) unsigned NOT NULL default '0',
			`epoch` int(10) unsigned NOT NULL default '0',
			`count` int(10) unsigned NOT NULL default '0',
			PRIMARY KEY  (`no`)
		)",
		"CREATE TABLE `v_tag` (
			`no` int(10) unsigned NOT NULL auto_increment,
			`id` varchar(40) NOT NULL default '',
			`object` varchar(40) NOT NULL default '',
			`type` varchar(20) default NULL,
			`name` varchar(80) default NULL,
			`author` varchar(70) default NULL,
			`epoch` int(10) unsigned default NULL,
			`tz` int(11) default NULL,
			`comment` text,
			PRIMARY KEY  (`no`)
		)"
	);

	while (list($key, $query) = each($queries)) {
		if (!mysql_query($query)) {
			return false;
		}
	}
	return true;
}

//
// Get the needed db version. This is purely a function so we don't have to use passed
// variables.
//
function checkneededdb() {
	$dbversion_needed = 3;
	return $dbversion_needed;
}

//
// Check and return the current schema version. Needed for updatedb() but will
// also be used just to check the version.
//
function checkdbversion() {
	// Check if we are able to check the current schema, mainly for backwards compat.
	$query = "DESCRIBE `gitstat_settings`";
	$result = mysql_query($query);
	if ($result == '') {
		// We are at 0 or 1
		$query = "DESCRIBE `Memcategory` `filename`";
		$result = mysql_query($query);
		$result2 = mysql_fetch_row($result);
		if ($result2 == '') {
			return '0';
		}
		return '1';
	}

	$query = "SELECT `data` FROM `gitstat_settings` WHERE `value` = 'dbversion'";
	$result = mysql_query($query);
	$data = mysql_fetch_array($result);

	return $data['data'];
}

//
// Update the schema to the last version
//
// dbver		Comments
// =========================================================================
// 0			Initial version
// 1			Single file monitoring
// 2			Addition of gitstat_settings table (including the dbversion)
// 3			Add visible flag to v_tag (adds flexibility in graph creation)
//
function updatedb() {

	$dbversion_current = checkdbversion();
	$dbversion_needed = checkneededdb();

	if ($dbversion_current == $dbversion_needed) {
		// We are done
		return true;
	}
	
	if ($dbversion_current == '0') {
		$query = "ALTER TABLE `Memcategory` ADD `filename` VARCHAR( 80 ) NOT NULL DEFAULT '0'";
		if (!mysql_query($query))
			return false;
		$dbversion_current++;
	}

	if ($dbversion_current == '1') {
		$query = "CREATE TABLE `gitstat_settings` (
			`value` VARCHAR(40) NOT NULL DEFAULT '',
			`data` TEXT,
			PRIMARY KEY `value` (`value`)
			)";
		if (!mysql_query($query))
			return false;
	
		$dbversion_current++;
		if (!writeconfig("dbversion", $dbversion_current))
			return false;
	}

	if ($dbversion_current == '2') {
		$query = "ALTER TABLE `v_tag` ADD `visible` BOOL NOT NULL DEFAULT TRUE";
		if (!mysql_query($query))
		return false;

		$dbversion_current++;
		if (!writeconfig("dbversion", $dbversion_current))
			return false;
	}

	// All new additions to the schema should be done here
	// Example:
	// if ($dbversion_current == '<needed - 1>' ) {
	//     $query = "<put your SQL statements here>";
	//     if (!mysql_query($query))
	//         return false;
	//
	//     $dbversion_current++;
	//     if (!writeconfig("dbversion", $dbversion_current))
	//         return false;
	// }

	return true;
}

//
// Write a configuration parameter to the database. Create or update if necessary.
//
function writeconfig($parameter, $value) {

	// Insert if $parameter is not present, update otherwise
	$query = "INSERT `gitstat_settings` (`value`, `data`) VALUES ('$parameter', '$value')
		ON DUPLICATE KEY UPDATE `data` = '$value'";
	if (!mysql_query($query))
		return false;
	else
		return true;
}
?>
