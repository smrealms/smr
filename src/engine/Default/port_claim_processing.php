<?php declare(strict_types=1);
$port = $player->getSectorPort();
$port->setRaceID($player->getRaceID());

$port->getLootHREF(true)->go();
