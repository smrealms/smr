<?php

function channel_msg_level($fp, $rdata) {

	global $channel, $nick;

	// in channel we only accept !rank
	// in private msg we accept both
	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s'.$channel.'\s:!level\s(.*)\s$/i', $rdata, $msg) ||
		preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s'.$nick.'\s:?level\s(.*)\s$/i', $rdata, $msg)) {

		echo_r($msg);
		$db = new SmrMySqlDatabase();
		$db->query('SELECT * FROM level WHERE level_id = ' . $msg[4]);
		if ($db->nextRecord()) {

			$level_name		= $db->getField('level_name');
			$experience		= $db->getField('requirement');

			fputs($fp, 'NOTICE '.$msg[1].' :For a '.$level_name.' you need to have '.$experience.' experience points!'.EOL);

		} else
			fputs($fp, 'NOTICE '.$msg[1].' :This Level doesn\'t exist!!'.EOL);

		return true;

	}

	return false;

}

?>