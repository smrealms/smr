<?php

function check_planet_builds()
{
	
}

function check_events($fp)
{
	global $events;

	foreach($events as $key => $event) {

		if ($event[0] < time()) {
			echo_r('[TIMER] finished. Sending a note to ' . $event[2]);
			fputs($fp, 'NOTICE ' . $event[2] . ' :' . $event[1] . EOL);
			unset($events[$key]);
		}

	}
}
?>