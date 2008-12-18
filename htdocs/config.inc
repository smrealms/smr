<?
	require_once('config.php');

	// hardware
	$SHIELDS		= 1;
	$ARMOR			= 2;
	$CARGO			= 3;
	$COMBATDRONES	= 4;
	$SCOUTDRONES	= 5;
	$MINES			= 6;
	$SCANNER		= 7;
	$CLOAK			= 8;
	$ILLUSION		= 9;
	$JUMP			= 10;
	$DCS			= 11;

	// planet
	$GENERATOR		= 1;
	$HANGAR			= 2;
	$TURRET			= 3;

	// message types
	$GLOBALMSG		= 1;
	$PLAYERMSG		= 2;
	$PLANETMSG		= 3;
	$SCOUTMSG		= 4;
	$POLITICALMSG	= 5;
	$ALLIANCEMSG	= 6;
	$ADMINMSG		= 7;

	// locations
	$GOVERNMENT		= 101;
	$UNDERGROUND	= 102;
	$FED			= 201;
	$RACIAL_SHIPS	= 400;
	$RACIAL_SHOPS	= 900;
	$RACE_WARS_SHIPS= 512;
	$RACE_WARS_WEPS = 326;
	$RACE_WARS_HARD = 607;
	
	//num races
	$NUM_RACES = 8;

	$TIME = TIME;
	
//	$smarty->assign( 'currentTime', $this->startTime );

	error_reporting(E_ALL | E_STRICT); // optional
	@date_default_timezone_set(@date_default_timezone_get());
	

	$STATS = array(
		'Experience Gain' => array( //in game
			'ID' => 1,
			'Description' => 'Gain more experience as a trader.',
			'Type' => array(1)
		),
		'Accuracy' => array(
			'ID' => 2,
			'Description' => 'Hit enemy ships more often.',
			'Type' => array(2)
		),
		'Maneuverability' => array(
			'ID' => 3,
			'Description' => 'Dodge incoming shots more often.',
			'Type' => array(1)
		),
		'Mechanics' => array(
			'ID' => 4,
			'Description' => 'Gain more ship condition per hour.',
			'Type' => array(1,2,4)
		),
		'Weapon Damage' => array(
			'ID' => 5,
			'Description' => 'Make your weapons hit for more damage against enemy ships.',
			'Type' => array(2)
		),/*
		'Weapon Power' => array( //replace this with something else (lower ship condition loss to techs, mine avoidance)
			'ID' => 6,
			'Description' => 'Carry more deadly weapons.',
			'Type' => array(3)
		),
		'Ship Size' => array(
			'ID' => 7,
			'Description' => 'Add anything from more cargo holds to more shields to more weapons to your ship.',
			'Type' => array(1,4)
		),*/
		'Trade Profit' => array(
			'ID' => 8,
			'Description' => 'Gain more money when trading.',
			'Type' => array(1)
		),
		'Relations' => array(
			'ID' => 9,
			'Description' => 'Increase your personal relations with different races, allowing weapons to be purchased and more ports to be traded at.',
			'Type' => array(1)
		),
		'Sensors' => array(
			'ID' => 10,
			'Description' => 'Have a better chance of seeing enemy\'s while they are cloaked.',
			'Type' => array(2)
		),
		'Stealth' => array(
			'ID' => 11,
			'Description' => 'Reduce your risk of being seen while cloaked, and avoid pinging scout drones.',
			'Type' => array(1)
		),
		'Bombardment' => array(
			'ID' => 12,
			'Description' => 'Do extra damage to stationary targets such as planets and ports.',
			'Type' => array(3)
		),
		'Cheaper Forces' => array(
			'ID' => 13,
			'Description' => 'Buy forces for a reduced price.',
			'Type' => array(4)
		),
		'Bigger Stacks' => array(
			'ID' => 14,
			'Description' => 'Lay more forces per sector.',
			'Type' => array(4)
		),
		'Planet Drone Resist' => array(
			'ID' => 15,
			'Description' => 'Take less damage from planetary drones.',
			'Type' => array(3)
		),
		'Shield Regen' => array(
			'ID' => 16,
			'Description' => 'Regenerate Shields over time.',
			'Type' => array(3)
		),
		'Planet Build' => array(
			'ID' => 17,
			'Description' => 'Build structures and produce goods faster on planets.',
			'Type' => array(4)
		)
	);
?>
