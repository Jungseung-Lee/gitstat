<?php

include "include/lib.php";

// Get some project specifics
$projectparams = getprojectconfig();

if (!strstr($_SERVER["HTTP_REFERER"],$projectparams['GITURL'])) {
        echo "<meta http-equiv='refresh' content='1;url=./index.php'>You're not allowed to access this page directly";
        return;
}

include ($projectparams['GRAPHMODULEDIR']."/jpgraph.php");
include ($projectparams['GRAPHMODULEDIR']."/jpgraph_pie.php");
include ($projectparams['GRAPHMODULEDIR']."/jpgraph_pie3d.php");

// The data for the pie chart

$list = array();

$each_data=split(":",$_GET['data']);

for ($i = 0; $i<count($each_data); $i++) {
	$detail_data = split(",",$each_data[$i]);
	$temp		 = array($detail_data[0]=>$detail_data[1]);
	array_push_associative($list,$temp);
}

if ($_GET['order'] == true) {
	arsort($list);
}

$data = array();
$labels = array();

if ($_GET['limit'] == true) {
	$index = 0;
	while (list($key,$value)=each($list)) {
		if ($index < $_GET['limit']) {
			// This solves the issue when $_GET['limit'] is greater than the number of pairs in $list.
			// The reason is that we append ':' when we pass data so that would make the last (empty) pair
			// valid.
			if ($key && $value) {
				array_push($data,$value);
				array_push($labels,$key);
			}
		} else {
			$etc+=$value;
		}
		$index ++;
	}

	// Don't show 'etc' when it's zero.
	if ($etc > 0) {
		array_push($data,$etc);
		array_push($labels,"etc");
	}
} else {
	while (list($key,$value)=each($list)) {
		if ($key==false)
			break;

		array_push($data,$value);
		array_push($labels,$key);
		}
}

// Callback function
// Get called with the actual value and should return the
// value to be displayed as a string
if ($_GET['float'] == true && $_GET['format'] == "h") {
	function cbFmtPercentage($aVal) {
		return sprintf("%.3fh",$aVal); // Convert to string
	}
} else if($_GET['float'] == true) {
	function cbFmtPercentage($aVal) {
		return sprintf("%.3f",$aVal); // Convert to string
	}
} else if ($_GET['format'] == "h") {
	function cbFmtPercentage($aVal) {
		return sprintf("%dh",$aVal); // Convert to string
	}
} else if ($_GET['format'] == "day") {
	function cbFmtPercentage($aVal) {
		return sprintf("%ddays",$aVal); // Convert to string
	}
} else {
	function cbFmtPercentage($aVal) {
		return sprintf("%d",$aVal); // Convert to string
	}
}

// Create the Pie Graph.
$graph = new PieGraph(600,400,"auto");
$graph->SetShadow();

// Set A title for the plot
$graph->title->Set($_GET['subject']);
$graph->title->SetColor("darkblue");
$graph->legend->Pos(0.04,0.1);

// Create 3D pie plot
$p1 = new PiePlot3d($data);
$graph->Add($p1);

$p1->SetTheme("sand");
$p1->SetCenter(0.37,0.5);
$p1->SetSize(170);
$p1->ExplodeSlice(0); 

// Adjust projection angle
$p1->SetAngle(80);

// Adjsut angle for first slice
$p1->SetStartAngle(90);

// Display the slice values
//$p1->value->SetFont(FF_ARIAL,FS_BOLD,11);
$p1->value->SetColor("navy");

// Add colored edges to the 3D pie
// NOTE: You can't have exploded slices with edges!
$p1->SetEdge("darkblue");

$p1->SetLabelType(PIE_VALUE_PER); 
//$p1->SetLabels($labels); 
$p1->SetLegends($labels);

$graph->Stroke();

?>
