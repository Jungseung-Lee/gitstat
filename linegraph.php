<?php

include "include/lib.php";

// Get some project specifics
$projectparams = getprojectconfig();

if (!strstr($_SERVER["HTTP_REFERER"],$projectparams['GITURL'])) {
        echo "<meta http-equiv='refresh' content='1;url=./index.php'>You're not allowed to access this page directly";
        return;
}

include ($projectparams['GRAPHMODULEDIR']."/jpgraph.php");
include ($projectparams['GRAPHMODULEDIR']."/jpgraph_line.php");

// The data for the line chart

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
		if ($index < 7) {
			array_push($data,$value);
			array_push($labels,$key);
		} else {
			$etc+=$value;
		}
		$index ++;
	}

	if ($index >= 7) {
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

// Create the graph. 
$graph = new Graph(600,350,"auto");	
$graph->SetScale("textlin");
$graph->title->Set($_GET['subject']);
$graph->ygrid->SetColor('gray','lightgray@0.5');

$graph->SetShadow();

$graph->SetMarginColor('lightgray@0.5');

$graph->xaxis->SetTickLabels($labels);

// Create a Line plot
$line1 = new LinePlot($data);

// Add the plot to the graph
$graph->Add($line1);

// Setup color for lines
if ($_GET['linecolor'] == "red") {
	$line1->SetColor("red","darkred",GRAD_VER);
	$line1->SetWeight(1);
} else {
	$line1->SetColor("steelblue","steelblue4",GRAD_VER);
}

if ($_GET['setybase'] == true) {
	//	$line1->SetYMin($_GET['setybase']); 
}	

if ($_GET['hidezero'] == true) {
	$line1->value->HideZero();
}

// Setup the callback function
$line1->value->SetFormatCallback("cbFmtPercentage");
$line1->value->Show();

// .. and send the graph back to the browser
$graph->Stroke();

?>
