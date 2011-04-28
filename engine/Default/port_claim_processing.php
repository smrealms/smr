<?php
require_once(get_file_loc('SmrPort.class.inc'));
$port =& SmrPort::getPort(SmrSession::$game_id,$player->getSectorID());
$port->setRaceID($player->getRaceID());

forward($port->getPortLootHref());

?>