<? 
include "include/lib.php";

$url=$_GET['url'];

if (logout()) {
	moveto($url);
} else {
	err('logout Error.','./index.php');
}
?>
