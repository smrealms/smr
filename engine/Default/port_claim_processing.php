<?php
require_once(get_file_loc('SmrPort.class.inc'));
$port =& SmrPort::getPort($player->getGameID(),$player->getSectorID());
$port->setRaceID($player->getRaceID());

forward($port->getLootHREF(true));

?>