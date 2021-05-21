<?php

include "include/lib.php";

// Get some project specifics
$projectparams = getprojectconfig();

if (!strstr($_SERVER["HTTP_REFERER"],$projectparams['GITURL'])) {
	echo "<meta http-equiv='refresh' content='1;url=./index.php'>You're not allowed to access this page directly";
	return;
}

include ($projectparams['GRAPHMODULEDIR']."/jpgraph.php");
include ($projectparams['GRAPHMODULEDIR']."/jpgraph_bar.php");

// The data for the bar chart

$list = array();

$each_data = split(":",$_GET['data']);

for ($i = 0; $i<count($each_data); $i++) {
		$detail_data = split(",",$each_data[$i]);
		$temp		 = array($detail_data[0]=>$detail_data[1]);
		array_push_associative($list,$temp);
}

arsort($list);

$datay = array();
$datax = array();

// Set the limit to 10 unless it's passed to us
if ($_GET['limit'] == true)
	$limit = $_GET['limit'];
else
	$limit = 10;


while (list($key,$value)=each($list)) {
	if($index < $limit){
		array_push($datay,$value);
		array_push($datax,$key);
	}
	$index ++;
}

// Size of graph
$width=600; 
$height=380;

// Set the basic parameters of the graph 
$graph = new Graph($width,$height,'auto');
$graph->SetScale("textlin");

// Rotate graph 90 degrees and set margin
$graph->Set90AndMargin(190,20,50,30);

// Nice shadow
$graph->SetShadow();

// Setup title
$graph->title->Set($_GET['subject']);
//$graph->title->SetFont(FF_VERDANA,FS_BOLD,14);
//$graph->subtitle->Set("(No Y-axis)");

// Setup X-axis
$graph->xaxis->SetTickLabels($datax);
//$graph->xaxis->SetFont(FF_VERDANA,FS_NORMAL,12);

// Some extra margin looks nicer
$graph->xaxis->SetLabelMargin(0);

// Label align for X-axis
$graph->xaxis->SetLabelAlign('right','center');

// Add some grace to y-axis so the bars doesn't go
// all the way to the end of the plot area
$graph->yaxis->scale->SetGrace(10);

// We don't want to display Y-axis
$graph->yaxis->Hide();

$graph->SetMarginColor('tan4@0.9');

// Now create a bar pot
$bplot = new BarPlot($datay);
$bplot->SetFillGradient("steelblue","steelblue4",GRAD_VER);
//$bplot->SetShadow();

//You can change the width of the bars if you like
//$bplot->SetWidth(0.5);

// We want to display the value of each bar at the top
$bplot->value->Show();
//$bplot->value->SetFont(FF_ARIAL,FS_BOLD,12);
$bplot->value->SetAlign('left','center');
$bplot->value->SetColor("black","darkred");
$bplot->value->SetFormat('%d');

// Add the bar to the graph
$graph->Add($bplot);

// .. and stroke the graph
$graph->Stroke();

?>
