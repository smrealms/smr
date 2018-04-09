<?php

function private_msg_weapon($fp, $rdata) {

	global $channel;
	$type=0;
	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s'.$channel.'\s:!weapon\sname\s(.*)\s$/i', $rdata, $msg_1))
	{
		$name = $msg_1[1];
		sleep(2);
		$type = 1;
	}
	elseif (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s'.$channel.'\s:!weapon\slevel\s(.*)\s(.*)\s$/i', $rdata, $msg_2))
	{
		$name = $msg_2[1];
		sleep(2);
		$type = 2;
	}
	elseif (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s'.$channel.'\s:!weapon\srange\s(.*)\s(.*)\s(.*)\s(.*)\s$/i', $rdata, $msg_3))
	{
		$name = $msg_3[1];
		sleep(2);
		$type = 3;
	}

	//first lets get our orders so we can make sure our query will work
	$a = array();
	$a[] = 'cost';
	$a[] = 'weapon_name';
	$a[] = 'shield_damage';
	$a[] = 'armour_damage';
	$a[] = 'accuracy';
	$a[] = 'race_id';
	$a[] = 'buyer_restriction';
	$a[] = 'power_level';
	$db = new SmrMySqlDatabase();

	if ($type == 1)
		$db->query('SELECT * FROM weapon_type JOIN race USING(race_id) WHERE weapon_name LIKE '.$db->escape_string('%' . $msg_1[4] . '%'));
	elseif ($type == 2) {

		if (in_array($msg_2[5], $a))
			$db->query('SELECT * FROM weapon_type JOIN race USING(race_id) WHERE power_level = '.$msg_2[4].' ORDER BY '.$msg_2[5].' DESC');
		else
			$db->query('SELECT * FROM weapon_type JOIN race USING(race_id) WHERE power_level = '.$msg_2[4]);

	} elseif ($type == 3) {

		//[4] = object
		//[5] = lower limit
		//[6] = upper limit
		//[7] = order
		//first make sure we arent flooding
		sleep(2);
		if (in_array($msg_3[4], $a) && in_array($msg_3[7], $a))
			$db->query('SELECT * FROM weapon_type JOIN race USING(race_id) WHERE '.$msg_3[4].' > '.$msg_3[5].' AND '.$msg_3[4].' < '.$msg_3[6].' ORDER BY '.$msg_3[7].' DESC');
		elseif (in_array($msg_3[4], $a))
			$db->query('SELECT * FROM weapon_type JOIN race USING(race_id) WHERE '.$msg_3[4].' > '.$msg_3[5].' AND '.$msg_3[4].' < '.$msg_3[6]);
		else {
			$rand = mt_rand(0,7);
			$object = $a[$rand];
			$db->query('SELECT * FROM weapon_type JOIN race USING(race_id) WHERE '.$object.' > '.$msg_3[5].' AND '.$object.' < '.$msg_3[6]);
		}

	}

	if ($db->getNumRows()) {

		fputs($fp, 'PRIVMSG '.$name.' :Name | Cost | Shield Damage | Armour Damage | Accuracy | Race | Restriction'.EOL);
		sleep(2);
		while ($db->nextRecord()) {

			$weapon_name	= $db->getField('weapon_name');
			$hardpoint	= $db->getField('power_level');
			$shield		= $db->getField('shield_damage');
			$armour		= $db->getField('armour_damage');
			$acc		= $db->getField('accuracy');
			$race		= $db->getField('race_name');
			$restrict	= $db->getField('buyer_restriction');
			$cost		= $db->getField('cost');

			$private_message = 'PRIVMSG '.$name.' :'.$weapon_name.' | '.$cost.' | '.$shield.' | '.$armour.' | '.$acc.' | '.$race.' | ';
			if ($restrict == 1)
				$private_message .= 'Good';
			elseif ($restrict == 2)
				$private_message .= 'Evil';
			else
				$private_message .= 'None';
			$private_message .= EOL;
			fputs($fp, $private_message);
			sleep(2);

		}

	} elseif ($type == 1)
		fputs($fp, 'PRIVMSG '.$name.' :There is no weapon called '.$msg_1[5].'!'.EOL);
	elseif ($type == 2)
		fputs($fp, 'PRIVMSG '.$name.' :There is no weapon with '.$msg_2[4].' power level!'.EOL);
	elseif ($type == 3)
		fputs($fp, 'PRIVMSG '.$name.' :There is no weapon in the cost range of '.$msg_3[4].' - '.$msg_3[5].'!'.EOL);

	if (isset($type))
		return true;

	return false;

}
