<?php

// Get the dates ("|" sets hr/min/sec to 0)
$start = DateTime::createFromFormat('d/m/Y|', $_REQUEST['game_start']);
$startTurns = empty($_REQUEST['game_start_turns']) ? $start :
              DateTime::createFromFormat('d/m/Y|', $_REQUEST['game_start_turns']);
$end = DateTime::createFromFormat('d/m/Y|', $_REQUEST['game_end']);

$game = SmrGame::createGame($var['game_id']);
$game->setName($_REQUEST['game_name']);
$game->setDescription($_REQUEST['desc']);
$game->setGameTypeID($_REQUEST['game_type']);
$game->setMaxTurns($_REQUEST['max_turns']);
$game->setStartTurnHours($_REQUEST['start_turns']);
$game->setMaxPlayers($_REQUEST['max_players']);
$game->setAllianceMaxPlayers($_REQUEST['alliance_max_players']);
$game->setAllianceMaxVets($_REQUEST['alliance_max_vets']);
$game->setStartDate($start->getTimestamp());
$game->setStartTurnsDate($startTurns->getTimestamp());
$game->setEndDate($end->getTimestamp());
$game->setGameSpeed($_REQUEST['game_speed']);
$game->setIgnoreStats($_REQUEST['ignore_stats'] == 'Yes');
$game->setStartingCredits($_REQUEST['starting_credits']);
$game->setCreditsNeeded($_REQUEST['creds_needed']);
$game->save();

$container = create_container('skeleton.php', '1.6/universe_create_sectors.php');
$container['message'] = '<span class="green">SUCCESS: edited game details</span>';
transfer('game_id');
forward($container);
