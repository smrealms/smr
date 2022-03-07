<?php declare(strict_types=1);

/**
 * @param resource $fp
 */
function check_events($fp): void {
	global $events;

	foreach ($events as $key => $event) {

		if ($event[0] < time()) {
			echo_r('[TIMER] finished. Sending a note to ' . $event[2]);
			fwrite($fp, 'NOTICE ' . $event[2] . ' :' . $event[1] . EOL);
			unset($events[$key]);
		}

	}
}
