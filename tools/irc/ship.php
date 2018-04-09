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

			fputs($fp, 'NOTICE '.$msg[1].' :' . str_pad('Name', $name_length) . ' | ' . str_pad('Hardpoints', $hp_length) . ' | ' . str_pad('Speed', $speed_length) . ' | ' . str_pad('Costs', $cost_length) . EOL);
			fputs($fp, 'NOTICE '.$msg[1].' :' . str_pad($ship_name, $name_length) . ' | ' . str_pad($hardpoint, $hp_length) . ' | ' . str_pad($speed, $speed_length) . ' | ' . str_pad($cost, $cost_length) . EOL);

		} else
			fputs($fp, 'NOTICE '.$msg[1].' :There is no ship called '.$msg[4].'!'.EOL);

		return true;

	}

	return false;

}
