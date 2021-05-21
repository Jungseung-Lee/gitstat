<?

include "include/lib.php";

// Get some project specifics
$diffdir = getprojectconfig("DIFFDIR");

$filepath = $diffdir."/".$_GET['file']; 
$dnfile = $_GET['file'];

if(eregi("(MSIE 5.0|MSIE 5.1|MSIE 5.5|MSIE 6.0)", $HTTP_USER_AGENT)) {
	 if(strstr($HTTP_USER_AGENT, "MSIE 5.5")) {
		header("Content-Type: doesn/matter");		
		header("Content-disposition: filename=$dnfile");		
		header("Content-Transfer-Encoding: binary");		
		header("Pragma: no-cache");		
		header("Expires: 0");		
	 }

	 if(strstr($HTTP_USER_AGENT, "MSIE 5.0")) {		
		header("Content-type: file/unknown");			
		header("Content-Disposition: attachment;filename=$dnfile");				
		header("Pragma: no-cache");				
		header("Expires: 0");					
	 }

	if(strstr($HTTP_USER_AGENT, "MSIE 5.1")) {
		header("Content-type: file/unknown");
		header("Content-Disposition: attachment;filename=$dnfile");
		header("Pragma: no-cache");
		header("Expires: 0");
	}

	if(strstr($HTTP_USER_AGENT, "MSIE 6.0")) {
		header("Content-type: application/x-msdownload");
		header("Content-Length: ".(string)(filesize("$filepath")));
		header("Content-Disposition: attachment; filename=$dnfile");
		header("Content-Transfer-Encoding: binary");
		header("Pragma: no-cache");
		header("Expires: 0");
	}
} else {
	clearstatcache();
	header("Content-type: file/unknown");
	header("Content-Length: ".(string)(filesize("$filepath")));
	header("Content-Disposition: attachment; filename=$dnfile");
	header("Pragma: no-cache");
	header("Expires: 0");
}
    
if (is_file("$filepath")) {
	$fp = fopen("$filepath", "rb");
	if (!fpassthru($fp))
		fclose($fp);
} else {
	echo "error";exit;
}

?>
