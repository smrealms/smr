<?php

// include helper funtions
include("course_plot.inc");

if (isset($var["from"])) $start = $var["from"];
else $start = $_POST["from"];
if (isset($var["to"])) $target = $var["to"];
else $target = $_POST["to"];

$plotter = new Course_Plotter();
$plotter->set_course($start,$target,$player->game_id);
$plotter->plot();

$account->log(5, "Player plots to $target.", $player->sector_id);

$container = array();
$container["url"] = "skeleton.php";
$container["body"] = "course_plot_result.php";

$container["plotted_course"] = serialize($plotter->plotted_course[1]);
$container["distance"] = $plotter->plotted_course[0];

$plotter->Course_Plotter_Destructor();
unset($plotter);

forward($container);

?>
