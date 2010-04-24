<?php

function msg_help($fp, $rdata) {

	global $channel, $nick;

	// global help?
	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s(.*)\s:!help\s$/i', $rdata, $msg)) {

		echo_r($msg);
		fputs($fp, 'NOTICE '.$msg[1].' :-- '.$nick.' HELP --'.EOL);
		fputs($fp, 'NOTICE '.$msg[1].' :!rank <nickname>         Displays the rank of the specified trader'.EOL);
		fputs($fp, 'NOTICE '.$msg[1].' :!level <rank>            Displays the experience requirement for the specified level'.EOL);
		fputs($fp, 'NOTICE '.$msg[1].' :!weapon level <level> <order>  Displays all weapons that have power level equal to <level> in the order specified (See !help weapon level)'.EOL);
		fputs($fp, 'NOTICE '.$msg[1].' :!weapon name <name>           Displays the weapon closest matching <name>'.EOL);
		fputs($fp, 'NOTICE '.$msg[1].' :!weapon range <object> <lower_limit> <upper_limit> <order>'.EOL);
		fputs($fp, 'NOTICE '.$msg[1].' :                         Displays all weapons that have <object> great than <lower_limit> and <object> less than <upper_limit> in order (see !help weapon range)'.EOL);
		fputs($fp, 'NOTICE '.$msg[1].' :!seen <nickname>         Displays the last time <nickname> was seen'.EOL);

		return true;

	// help on a spec command?
	} elseif (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s(.*)\s:!help\s(.*)\s$/i', $rdata, $msg)) {

		echo_r($msg);
		if ($msg[5] == 'login')
			fputs($fp, 'NOTICE '.$msg[1].' :No help available yet! Ask MrSpock!'.EOL);
		elseif ($msg[5] == '!rank')
			fputs($fp, 'NOTICE '.$msg[1].' :No help available yet! Ask MrSpock!'.EOL);
		elseif ($msg[5] == '!level')
			fputs($fp, 'NOTICE '.$msg[1].' :No help available yet! Ask MrSpock!'.EOL);
		elseif ($msg[5] == 'weapon level') {

			fputs($fp, 'NOTICE '.$msg[1].' :Syntax !weapon level <level> <order>'.EOL);
			fputs($fp, 'NOTICE '.$msg[1].' :Returns all weapons that are level <level> in order <order>'.EOL);
			fputs($fp, 'NOTICE '.$msg[1].' :Example !weapon level 4 shield_damage would return the level 4 power weapons ordered by the amount of shield damage they do.'.EOL);
			fputs($fp, 'NOTICE '.$msg[1].' :<order> options are cost, shield_damage, armour_damage, buyer_restriction, race_id, accuracy, and weapon_name'.EOL);
			fputs($fp, 'NOTICE '.$msg[1].' :All "order" commands must be spelt correctly'.EOL);
			fputs($fp, 'NOTICE '.$msg[1].' :See Azool for additional help on this topic'.EOL);

		} elseif ($msg[5] == 'weapon range') {

			fputs($fp, 'NOTICE '.$msg[1].' :Syntax !weapon range <object> <cost1> <cost2> <order>'.EOL);
			fputs($fp, 'NOTICE '.$msg[1].' :Returns all weapons that have <object> greater than <lower_limit> and less than <upper_limit> in the order <order>'.EOL);
			fputs($fp, 'NOTICE '.$msg[1].' :Example !weapon range cost_range 100000 200000 shield_damage would return all weapons whose costs are between 100000 and 200000 ordered by the amount of shield damage they do.'.EOL);
			fputs($fp, 'NOTICE '.$msg[1].' :<object> and <order> options are cost, shield_damage, armour_damage, buyer_restriction, race_id, accuracy, power_level, and weapon_name'.EOL);
			fputs($fp, 'NOTICE '.$msg[1].' :All "order" and "object" commands must be spelt correctly'.EOL);
			fputs($fp, 'NOTICE '.$msg[1].' :See Azool for additional help on this topic'.EOL);

		} else
			fputs($fp, 'NOTICE '.$msg[1].' :There is no help available for this command! Try !help'.EOL);

		return true;

	}

	return false;

}

?>