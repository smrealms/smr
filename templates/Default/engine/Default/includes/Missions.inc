<?php

$AvailableMissions =& $ThisPlayer->getAvailableMissions();
if(count($AvailableMissions) > 0)
{
	foreach($AvailableMissions as &$AvailableMission)
	{
		echo $AvailableMission['Steps'][0]['Text']; ?><br />
		<div class="buttonA"><a href="<?php /*TODO*/ ?>" class="buttonA"> Accept (TODO) </a></div><?php
	}
}

?>