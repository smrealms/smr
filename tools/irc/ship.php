<?php

function channel_msg_ship($fp, $rdata) {

	global $channel;

	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s'.$channel.'\s:!ship\s(.*)\s$/i', $rdata, $msg)) {

		echo_r($msg);
		$db = new SmrMySqlDatabase();
		$db->query('SELECT * FROM ship_type WHERE ship_name LIKE '.$db->escape_string('%' . $msg[4] . '%'));
		if ($db->nextRecord()) {

			$ship_name	= $db->getField('ship_name');
			$hardpoint	= $db->getField('hardpoint');
			$speed		= $db->getField('speed');
			$cost		= $db->getField('cost');

			$name_length = strlen($ship_name);
			$hp_length = strlen('Hardpoints');
			$speed_length = strlen('Speed');
			$cost_length = max(strlen('Costs'), strlen($cost));

			fputs($fp, 'NOTICE '.$msg[1].' :' . fill_string('Name', $name_length) . ' | ' . fill_string('Hardpoints', $hp_length) . ' | ' . fill_string('Speed', $speed_length) . ' | ' . fill_string('Costs', $cost_length) . EOL);
			fputs($fp, 'NOTICE '.$msg[1].' :' . fill_string($ship_name, $name_length) . ' | ' . fill_string($hardpoint, $hp_length) . ' | ' . fill_string($speed, $speed_length) . ' | ' . fill_string($cost, $cost_length) . EOL);

		} else
			fputs($fp, 'NOTICE '.$msg[1].' :There is no ship called '.$msg[4].'!'.EOL);

		return true;

	}

	return false;

}

?>