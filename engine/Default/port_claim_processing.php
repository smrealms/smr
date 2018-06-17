<?php
$port = $player->getSectorPort();
$port->setRaceID($player->getRaceID());

forward($port->getLootHREF(true));
