<?php
try
{
	echo '<pre>';
	require_once('../htdocs/config.inc');
	require_once(LIB . 'Default/SmrMySqlDatabase.class.inc');
	require_once(LIB . 'Default/Globals.class.inc');

	$forwardedCount = 0;
	function overrideForward($container)
	{
		if($container['body']=='error.php') //We hit a create_error - this shouldn't happen for an NPC often, for now we want to throw an exception for it for testing.
			throw new Exception($container['message']);
		throw new Exception('Forward'); //We catch and check for exceptions with the message "Forward" in NPCStuff, very hacky but works - need to extend Exception for a less over-arching catch.
											//We have to throw the exception to get back up the stack, otherwise we quickly hit problems of overflowing the stack.
	}
	define('OVERRIDE_FORWARD',true);
	
	define('NPCScript',true);
	
	require_once(get_file_loc('smr.inc'));
	require_once(get_file_loc('SmrAccount.class.inc'));

	$NPC_LOGINS_USED = array('');

	changeNPCLogin();
	
	define('NPC_GAME_ID',108);
	define('NPC_LOW_TURNS',75);
	define('NPC_START_TURNS',300);

	$HIDDEN_PLAYERS = array();




	require_once(get_file_loc('Plotter.class.inc'));
	require_once(get_file_loc('RouteGenerator.class.inc'));
	require_once(get_file_loc('shop_goods.inc'));

	NPCStuff();
}
catch(Exception $e)
{
	global $account,$var,$player,$NPC_LOGIN;
	$errorType = 'Error';
	$message='';
	$currMySQLError='';
	if(is_object($account))
	{
		$message .= 'Login: '.$account->login.EOL.EOL.'-----------'.EOL.EOL.
			'Account ID: '.$account->account_id.EOL.EOL.'-----------'.EOL.EOL.
			'E-Mail: '.$account->email.EOL.EOL.'-----------'.EOL.EOL;
	}
	$message .= 'Error Message: '.$e->getMessage().EOL.EOL.'-----------'.EOL.EOL;
	if($currMySQLError = mysql_error())
	{
		$errorType = 'Database Error';
		$message .= 'MySQL Error MSG: '.mysql_error().EOL.EOL.'-----------'.EOL.EOL;
	}
	$message .=	'Trace MSG: '.$e->getTraceAsString().EOL.EOL.'-----------'.EOL.EOL.
		'$var: '.var_export($var,true).EOL.EOL.'-----------'.EOL.EOL.
		'USING_AJAX: '.(defined('USING_AJAX')?var_export(USING_AJAX,true):'undefined');
		
	try
	{
		if(function_exists('release_lock'))
			release_lock(); //Try to release lock so they can carry on normally
	}
	catch(Exception $ee)
	{
		$message .= EOL.EOL.'-----------'.EOL.EOL.
					'Releasing Lock Failed' .EOL.
					'Message: ' . $ee->getMessage() .EOL.EOL;
		if($currMySQLError!=mysql_error())
		{
			$message .= 'MySQL Error MSG: '.mysql_error().EOL.EOL;
		}
		$message .= 'Trace: ' . $ee->getTraceAsString();
	}
		
	try
	{
		$db = new SmrMySqlDatabase();
		$db->query('UPDATE npc_logins SET working='.$db->escapeBoolean(false).' WHERE login='.$db->escapeString($NPC_LOGIN));
		if($db->getChangedRows()>0)
			debug('Unlocked NPC: '.$NPC_LOGIN);
		else
			debug('Failed to unlock NPC: '.$NPC_LOGIN);
	}
	catch(Exception $ee)
	{
		$message .= EOL.EOL.'-----------'.EOL.EOL.
					'Releasing NPC Failed' .EOL.
					'Message: ' . $ee->getMessage() .EOL.EOL;
		if($currMySQLError!=mysql_error())
		{
			$message .= 'MySQL Error MSG: '.mysql_error().EOL.EOL;
		}
		$message .= 'Trace: ' . $ee->getTraceAsString();
	}
	
	var_dump($message);
	exit;
}
		
function NPCStuff()
{
	global $account,$actions;
	
	$actions=-1;
//	for($i=0;$i<40;$i++)
	while(true)
	{
		$actions++;
		try
		{
			debug('Action #'.$actions);
			
			if($actions==0)
			{ //Auto-create player if need be.
				$db = new SmrMySqlDatabase();
				$db->query('SELECT * FROM player WHERE account_id = '.$account->getAccountID().' AND game_id = '.NPC_GAME_ID.' LIMIT 1');
				if(!$db->nextRecord())
				{
					debug('Auto-creating player: '.$account->getLogin());
					$actions--;
					processContainer(joinGame(SmrSession::$game_id,$account->getLogin()));
				}
			}

			SmrSession::$game_id = NPC_GAME_ID;
			
			//We have to reload player on each loop
			$player	=& SmrPlayer::getPlayer($account->getAccountID(), SmrSession::$game_id);
			$GLOBALS['player'] =& $player;
			
			if($actions==0)
			{
				if($player->getTurns()<NPC_START_TURNS && $player->hasNewbieTurns() && $player->hasFederalProtection())
				{
					debug('We don\'t have enough turns to bother starting trading, and we are protected: '.$player->getTurns());
					changeNPCLogin();
				}
			}
			
			$TRADE_ROUTES =& findRoutes();
			if(!isset($TRADE_ROUTE)) //We only want to change trade route if there isn't already one set.
				$TRADE_ROUTE =& changeRoute($TRADE_ROUTES);
			
			if($player->getShip()->isUnderAttack()===true&&($player->hasPlottedCourse()===false||SmrSector::getSector($player->getGameID(),$player->getPlottedCourse()->getEndSectorId())->offersFederalProtection()===false))
			{ //We're under attack and need to plot course to fed.
				debug('Under Attack');
				processContainer(plotToFed());
			}
			else if($player->hasPlottedCourse()===true)
			{ //We have a route to follow, figure it's probably a sensible thing to follow.
				debug('Follow Course: '.$player->getPlottedCourse()->getNextOnPath());
				processContainer(moveToSector($player->getPlottedCourse()->getNextOnPath()));
			}
			else if($player->getTurns()<NPC_LOW_TURNS)
			{ //We're low on turns and need to plot course to fed
				debug('Low Turns');
				if($player->hasNewbieTurns())
				{ //We have newbie turns, we can just wait here.
					debug('We have newbie turns, let\'s just switch to another NPC.');
					changeNPCLogin();
				}
				processContainer(plotToFed());
			}
			else if($TRADE_ROUTE instanceof Route)
			{
//				var_dump($TRADE_ROUTE);
				debug('Trade Route');
				$forwardRoute =& $TRADE_ROUTE->getForwardRoute();
				$returnRoute =& $TRADE_ROUTE->getReturnRoute();
				if($forwardRoute->getBuySectorId()==$player->getSectorID()||$returnRoute->getBuySectorId()==$player->getSectorID())
				{
					if($forwardRoute->getBuySectorId()==$player->getSectorID())
					{
						$buyRoute =& $forwardRoute;
						$sellRoute =& $returnRoute;
					}
					else if($returnRoute->getBuySectorId()==$player->getSectorID())
					{
						$buyRoute =& $returnRoute;
						$sellRoute =& $forwardRoute;
					}
					
					$ship =& $player->getShip();
					if($ship->getUsedHolds()>0)
					{
						if($ship->hasCargo($sellRoute->getGoodID()))
						{ //Sell goods
							$goodID = $sellRoute->getGoodID();
							
							$port =& $player->getSector()->getPort();
							$tradeable = checkPortTradeable($port,$player);
							
							if($tradeable===true && $port->getGoodAmount($goodID)>=$ship->getCargo($sellRoute->getGoodID())) //TODO: Sell what we can rather than forcing sell all at once?
							{ //Sell goods
								debug('Sell Goods');
								processContainer(tradeGoods($goodID,$player,$port));
							}
							else
							{
								//Move to next route or fed.
								if(($TRADE_ROUTE =& changeRoute($TRADE_ROUTES))===false)
								{
//									var_dump($TRADE_ROUTES);
									debug('Changing Route Failed');
									processContainer(plotToFed());
								}
								else
								{
									debug('Route Changed');
									continue;
								}
							}
						}
						else if($ship->hasCargo($buyRoute->getGoodID())===true)
						{ //We've bought goods, plot to sell
							debug('Plot To Sell: '.$buyRoute->getSellSectorId());
							processContainer(plotToSector($buyRoute->getSellSectorId()));
						}
						else
						{
							//Dump goods
							debug('Dump Goods');
							processContainer(dumpCargo());
						}
					}
					else
					{ //Buy goods
						$goodID = $buyRoute->getGoodID();
						
						$port =& $player->getSector()->getPort();
						$tradeable = checkPortTradeable($port,$player);
		
						if($tradeable===true && $port->getGoodAmount($goodID)>=$ship->getEmptyHolds())
						{ //Buy goods
							debug('Buy Goods');
							processContainer(tradeGoods($goodID,$player,$port));
						}
						else
						{
							//Move to next route or fed.
							if(($TRADE_ROUTE =& changeRoute($TRADE_ROUTES))===false)
							{
//								var_dump($TRADE_ROUTES);
								debug('Changing Route Failed');
								processContainer(plotToFed());
							}
							else
							{
								debug('Route Changed');
								continue;
							}
						}
					}
				}
				else
				{
					debug('Plot To Buy: '.$forwardRoute->getBuySectorId());
					processContainer(plotToSector($forwardRoute->getBuySectorId()));
				}
			}
			else
			{ //Otherwise let's run around at random.
				$links = $player->getSector()->getLinks();
				$moveTo = $links[array_rand($links)];
				debug('Random Wanderings: '.$moveTo);
				processContainer(moveToSector($moveTo));
			}
		}
		catch(Exception $e)
		{
			if($e->getMessage()!='Forward')
				throw $e;
			global $lock;
			if($lock)
			{ //only save if we have the lock.
				SmrSector::saveSectors();
				SmrShip::saveShips();
				SmrPlayer::savePlayers();
				SmrForce::saveForces();
				SmrPort::savePorts();
				release_lock();
				//Clean up the caches as the data may get changed by other players
				SmrSector::clearCache();
				SmrPlayer::clearCache();
				SmrForce::clearCache();
				SmrPort::clearCache();
				//Have a sleep between actions
				sleepNPC();
			}
		}
	}
	debug('Actions Finished.');
	exitNPC();
}

function debug($message)
{
	echo date('Y-m-d H:i:s - ').$message.EOL;
}

function processContainer($container)
{
	var_dump($container);
	resetContainer($container);
	do_voodoo();
}

function sleepNPC()
{
	sleep(mt_rand(10,15)/10); //Sleep for a random time between 1-1.5s
}

function exitNPC()
{
	global $NPC_LOGIN;
	if($NPC_LOGIN!==null)
	{
		$db = new SmrMySqlDatabase();
		$db->query('UPDATE npc_logins SET working='.$db->escapeBoolean(false).' WHERE login='.$db->escapeString($NPC_LOGIN));
		if($db->getChangedRows()>0)
			debug('Unlocked NPC: '.$NPC_LOGIN);
		else
			debug('Failed to unlock NPC: '.$NPC_LOGIN);
	}
	else
		debug('NPC_LOGIN is null.');
	release_lock();
	exit;
}

function changeNPCLogin()
{
	global $NPC_LOGIN,$actions,$account,$NPC_LOGINS_USED;
	$actions=0;
	$db = new SmrMySqlDatabase();
	$db->query('UPDATE npc_logins SET working='.$db->escapeBoolean(false).' WHERE login='.$db->escapeString($NPC_LOGIN));
	if($db->getChangedRows()>0)
		debug('Unlocked NPC: '.$NPC_LOGIN);
	else
		debug('Failed to unlock NPC: '.$NPC_LOGIN);
	
	$NPC_LOGIN = null;
	
	debug('Choosing new NPC');
	$db2 = new SmrMySqlDatabase();
	$db->query('SELECT login FROM npc_logins WHERE working='.$db->escapeBoolean(false).' AND login NOT IN ('.$db->escapeArray($NPC_LOGINS_USED).')');
	while($db->nextRecord())
	{
		$db2->query('UPDATE npc_logins SET working='.$db2->escapeBoolean(true).' WHERE login='.$db2->escapeString($db->getField('login')).' AND working='.$db2->escapeBoolean(false));
		if($db2->getChangedRows()>0)
		{
			$NPC_LOGIN = $db->getField('login');
			break;
		}
	}
	$NPC_LOGINS_USED[] = $NPC_LOGIN;

	if($NPC_LOGIN===null)
	{
		debug('No free NPCs');
		exitNPC();
	}
	debug('Chosen NPC: '.$NPC_LOGIN);

	if(SmrAccount::getAccountByName($NPC_LOGIN)==null)
	{
		debug('Creating account for: '.$NPC_LOGIN);
		$account = SmrAccount::createAccount($NPC_LOGIN,'21sdgasdg,s..,23','NPC@smrealms.de','NPC','NPC','NPC','NPC','NPC','NPC','NPC',0,0);
		$account->validated = 'TRUE';
		$account->update();
	}
	else
	{
		$account = SmrAccount::getAccountByName($NPC_LOGIN);
	}

	SmrSession::$account_id = $account->getAccountID();
}

function tradeGoods($goodID,AbstractSmrPlayer &$player,SmrPort &$port)
{
	sleepNPC(); //We have an extra sleep at port to make the NPC more vulnerable.
	$ship =& $player->getShip();
	$portRelations = Globals::getRaceRelations($player->getGameID(),$port->getRaceID());
	$relations = $player->getRelation($port->getRaceID()) + $portRelations[$player->getRaceID()];

	$portGood = $port->getGood($goodID);
	
	if($portGood['TransactionType'] == 'Buy')
		$amount = $ship->getEmptyHolds();
	else
		$amount = $ship->getCargo($goodID);

	$idealPrice = $port->getIdealPrice($goodID, $portGood['TransactionType'], $amount, $relations);
	$offeredPrice = $port->getOfferPrice($idealPrice, $relations, $portGood['TransactionType']);
	
	return create_container('shop_goods_processing.php','',array('offered_price'=>$offeredPrice,'ideal_price'=>$idealPrice,'amount'=>$amount,'good_id'=>$goodID,'bargain_price'=>$offeredPrice));
}
function dumpCargo()
{
	global $player;
	$ship =& $player->getShip();
	$cargo =& $ship->getCargo();
	foreach($cargo as $goodID => $amount)
	{
		if($amount > 0)
		{
			return create_container('cargo_dump_processing.php','',array('good_id'=>$goodID,'amount'=>$amount));
		}
	}
}
function plotToSector($sectorID)
{
	global $player;
	return create_container('course_plot_processing.php','',array('from'=>$player->getSectorID(),'to'=>$sectorID));
}

function plotToFed()
{
	global $player;
	debug('Plotting To Fed');
	
	if($player->getSector()->offersFederalProtection())
	{
		if(!$player->hasNewbieTurns() && !$player->hasFederalProtection() && $player->getShip()->hasIllegalGoods())
		{ //We have illegals and no newbie turns, dump the illegals to get fed protection.
			debug('Dumping illegals');
			processContainer(dumpCargo());
		}
		debug('Plotted to fed whilst in fed, switch NPC and wait for turns');
		changeNPCLogin();
	}
	
	$_REQUEST['xtype'] = 'Locations';
	$_REQUEST['X'] = 'Fed';
	
	return create_container('course_plot_nearest_processing.php');
}
function moveToSector($targetSector)
{
	return create_container('sector_move_processing.php','',array('target_sector'=>$targetSector,'target_page'=>''));
}

function &changeRoute(array &$tradeRoutes)
{
	$false = false;
	if(count($tradeRoutes)==0)
		return $false;
	$routeKey = array_rand($tradeRoutes);
	$tradeRoute =& $tradeRoutes[$routeKey];
	unset($tradeRoutes[$routeKey]);
	return $tradeRoute;
}

function joinGame($gameID,$playerName)
{
	global $NPC_LOGIN;
	debug('Creating player for: '.$NPC_LOGIN);
	$races =& Globals::getRaces();
	
	$_REQUEST['player_name'] = $playerName;
	$_REQUEST['race_id'] = array_rand(array_keys($races));
	return create_container('game_join_processing.php','',array('game_id'=>NPC_GAME_ID));
}

function &findRoutes()
{
	global $player;
	
	$galaxies =& SmrGalaxy::getGameGalaxies($player->getGameID());
	
	$tradeGoods = array(GOOD_NOTHING => false);
	$goods =& Globals::getGoods();
	foreach($goods as $goodID => &$good)
	{
		if($player->meetsAlignmentRestriction($good['AlignRestriction']))
			$tradeGoods[$goodID] = true;
		else
			$tradeGoods[$goodID] = false;
	} unset($good);
	$tradeRaces = array();
	$races =& Globals::getRaces();
	foreach($races as $raceID => &$race)
	{
		$tradeRaces[$raceID] = false;
	} unset($race);
	$tradeRaces[$player->getRaceID()] = true;
	
	$galaxy =& $player->getSector()->getGalaxy();
	
	$maxNumberOfPorts = 2;
	$routesForPort=-1;
	$numberOfRoutes=1000;
	$maxDistance=15;
	
	$startSectorID=$galaxy->getStartSector();
	$endSectorID=$galaxy->getEndSector();
	
	$db = new SmrMySqlDatabase();
	$db->query('SELECT routes FROM route_cache WHERE game_id='.$db->escapeNumber($player->getGameID()).' AND max_ports='.$db->escapeNumber($maxNumberOfPorts).' AND goods_allowed='.$db->escapeObject($tradeGoods).' AND races_allowed='.$db->escapeObject($tradeRaces).' AND start_sector_id='.$db->escapeNumber($startSectorID).' AND end_sector_id='.$db->escapeNumber($endSectorID).' AND routes_for_port='.$db->escapeNumber($routesForPort).' AND max_distance='.$db->escapeNumber($maxDistance));
	if($db->nextRecord())
	{
		$routes = unserialize(gzuncompress($db->getField('routes')));
		return $routes;
	}
	else
	{
		$allSectors = array();
		foreach($galaxies as &$galaxy)
		{
			$allSectors += $galaxy->getSectors(); //Merge arrays
		} unset($galaxy);
		
		$distances =& Plotter::calculatePortToPortDistances($allSectors,$maxDistance,$startSectorID,$endSectorID);
		
		
		if ($maxNumberOfPorts == 1)
			$allRoutes = RouteGenerator::generateOneWayRoutes($allSectors, $distances, $tradeGoods, $tradeRaces, $routesForPort);
		else
			$allRoutes = RouteGenerator::generateMultiPortRoutes($maxNumberOfPorts, $allSectors, $tradeGoods, $tradeRaces, $distances, $routesForPort, $numberOfRoutes);
		
		unset($distances);
		
		$allRoutes =& $allRoutes[RouteGenerator::EXP_ROUTE];
		$routesMerged = array();
		foreach($allRoutes as $multi => &$routesByMulti)
		{
			$routesMerged += $routesByMulti; //Merge arrays
		} unset($routesByMulti);
		
		unset($allSectors);
		SmrPort::clearCache();
		SmrSector::clearCache();
		$db->query('INSERT INTO route_cache ' .
				'(game_id, max_ports, goods_allowed, races_allowed, start_sector_id, end_sector_id, routes_for_port, max_distance, routes)' .
				' VALUES ('.$db->escapeNumber($player->getGameID()).', '.$db->escapeNumber($maxNumberOfPorts).', '.$db->escapeObject($tradeGoods).', '.$db->escapeObject($tradeRaces).', '.$db->escapeNumber($startSectorID).', '.$db->escapeNumber($endSectorID).', '.$db->escapeNumber($routesForPort).', '.$db->escapeNumber($maxDistance).', '.$db->escapeObject($routesMerged,true).')');
		return $routesMerged;
	}
}
?>