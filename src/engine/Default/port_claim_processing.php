<?php declare(strict_types=1);
$port = $player->getSectorPort();
$port->setRaceID($player->getRaceID());

forward($port->getLootHREF(true));
