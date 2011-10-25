<?php
require_once(get_file_loc('Plotter.class.inc'));
$sector =& $player->getSector();

$template->assign('PageTopic','Plot A Course');

require_once(get_file_loc('menue.inc'));
create_nav_menue($template, $player);

$path = unserialize($var['Distance']);

$PHP_OUTPUT.= '<table cellspacing="0" cellpadding="0" style="width:100%;border:none"><tr><td style="padding:0px;vertical-align:top">';
$PHP_OUTPUT.=('The plotted course is ' . $path->getTotalSectors() . ' sectors long and '.$path->getTurns().' turns.');
$PHP_OUTPUT.= '</td><td style="padding:0px;vertical-align:top;width:32em">';

// get the array back

$full = (implode(' - ', $path->getPath()));

// throw start sector away
// it's useless for the route
$path->removeStart();

// now get the sector we are going to but don't remove it (sector_move_processing does it)
$next_sector = $path->getNextOnPath();

if ($sector->isLinked($next_sector)) {

	// save this to db (if we still have something
	if ($path->getTotalSectors()>0)
	{
		$player->setPlottedCourse($path);
	}

	//$PHP_OUTPUT.=create_echo_form($container);
	if (!$player->isLandedOnPlanet())
	{
		$nextSector =& SmrSector::getSector($player->getGameID(),$path->getNextOnPath(),$player->getAccountID());
	
		$PHP_OUTPUT.='<table class="nobord" width="100%">
			<tr>
				<td class="top right">
					<div class="buttonA">
						<a class="buttonA" href="'.$nextSector->getCurrentSectorHREF().'">&nbsp; Follow Course ('.$path->getNextOnPath().') &nbsp;</a>
					</div>
				</td>
			</tr>';
		if($ship->hasScanner())
		{
			$PHP_OUTPUT.='<tr>
				<td class="top right">
					<div class="buttonA">
						<a class="buttonA" href="'.$nextSector->getScanSectorHREF().'">&nbsp; Scan Course ('.$path->getNextOnPath().') &nbsp;</a>
					</div>
				</td>
			</tr>';
		}
		$PHP_OUTPUT.='</table>';
	}
}

$PHP_OUTPUT.= '</td></tr></table><br /><h2>Plotted Course</h2><br />';
$PHP_OUTPUT.= $full;

?>