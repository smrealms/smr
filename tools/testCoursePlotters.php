<?php

require_once('/home/page/SMR/htdocs/config.inc');
require_once(LIB . 'Default/smr.inc');
require_once(get_file_loc('Unused/course_plot.inc'));

testCourseDistances(2);

function testCourseDistances($gameID)
{
	//Initialise cache for fairness
	$galaxies =& SmrGalaxy::getGameGalaxies($gameID);
	$galaxySectors = array();
	foreach($galaxies as &$galaxy)
	{
		$galaxiesSectors[] =& $galaxy->getSectors();
	} unset($galaxy);
	//Test plotters
	$newTime = 0;
	$oldTime = 0;
	foreach($galaxiesSectors as &$galaxySectors)
	{
		foreach($galaxySectors as &$galaxySector)
		{
			foreach($galaxiesSectors as &$targetGalaxySectors)
			{
				foreach($targetGalaxySectors as &$targetGalaxySector)
				{
					if(!$galaxySector->equals($targetGalaxySector))
					{
						$time = microtime(true);
						$newDI = getPlotDistanceNew($galaxySector,$targetGalaxySector);
						$newTime += microtime(true) - $time;
						
						$time = microtime(true);
						$oldDI = getPlotDistanceOld($galaxySector,$targetGalaxySector);
						$oldTime += microtime(true) - $time;
						
						if($newDI!=$oldDI)
						{
							echo 'Difference, new: '.$newDI.', old:'.$oldDI.', sector1:'.$galaxySector->getSectorID().', sector2:'.$targetGalaxySector->getSectorID().EOL;
						}
					}
				} unset($targetGalaxySector);
				echo 'New time: '.$newTime.', old time:'.$oldTime.EOL;
			} unset($targetGalaxySectors);
		} unset($galaxySector);
	} unset($galaxySectors);
	echo 'New time: '.$newTime.', old time:'.$oldTime.EOL;
}


function getPlotDistanceNew(SmrSector &$sector,SmrSector &$target)
{
	$path =& Plotter::findDistanceToX($target, $sector, true);
	return $path->getRelativeDistance();
}

function getPlotDistanceOld(SmrSector &$sector,SmrSector &$target)
{
	$plotter = new Course_Plotter();
	$plotter->set_course($sector->getSectorID(),$target->getSectorID(),$sector->getGameID());
	$plotter->plot();
	$return = $plotter->plotted_course[0];
	$plotter->Course_Plotter_Destructor();
	unset($plotter);
	return $return;
}
