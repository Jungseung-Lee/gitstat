<?

include "include/lib.php";

// Get some project specifics
$giturl = getprojectconfig("GITURL");

if (!strstr($_SERVER["HTTP_REFERER"],$giturl)) {
        echo "<meta http-equiv='refresh' content='1;url=./index.php'>You're not allowed to access this page directly";
        return;
}

dbconnect();

// Loop through all the tags
$query = "SELECT no,name,visible FROM v_tag";
$result = mysql_query($query);
while ($data=mysql_fetch_array($result)) {
	// Is the checkbox for this tag checked?
	if ($_POST[("tag".$data['no'])] == true)
		$newval = 1;
	else
		$newval = 0;

	// Only update the database if we have a change
	if($data['visible'] != $newval){
		$query2 = "UPDATE v_tag SET visible=".$newval." WHERE name='".$data['name']."'";
		mysql_query($query2);
	}
}

dbclose();

?>
<body onload="alert('Your modifications were changed successfully');"><?moveto("./admin.php?part=6");?>
<?
