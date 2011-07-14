<?php
@date_default_timezone_set(@date_default_timezone_get());
//ini_set('display_errors', TRUE);
//ini_set('display_startup_errors', TRUE);

define('ACCOUNT_ID_PORT',65535);
define('ACCOUNT_ID_ADMIN',65534);
define('ACCOUNT_ID_PLANET',65533);
define('ACCOUNT_ID_GROUP_RACES',65500);
define('ACCOUNT_ID_NHL',36);

define('TIME_BEFORE_NEWBIE_TIME',3600); //1 hour
define('TIME_FOR_COUNCIL_VOTE',259200); //3 days
define('TIME_FOR_WAR_VOTE_FED_SAFETY',259200); //3 days


define('IRC_BOT_SOCKET', '/tmp/ircbot.sock');
define('MULTI_CHECKING_COOKIE_VERSION','v3');

require_once('config.specific.php');

/*
 * Localisations
 */
define('DEFAULT_DATE_DATE_SHORT','j/n/Y');
define('DEFAULT_DATE_TIME_SHORT','g:i:s A');
define('DEFAULT_DATE_FULL_SHORT',DEFAULT_DATE_DATE_SHORT.' '.DEFAULT_DATE_TIME_SHORT);
define('DEFAULT_DATE_FULL_SHORT_SPLIT',DEFAULT_DATE_DATE_SHORT.'\<b\r /\>'.DEFAULT_DATE_TIME_SHORT);
define('DEFAULT_DATE_FULL_LONG','l jS F '.DEFAULT_DATE_TIME_SHORT);

/*
 * Race types
 */

define('RACE_NEUTRAL',1);
define('RACE_ALSKANT',2);
define('RACE_CREONTI',3);
define('RACE_HUMAN',4);
define('RACE_IKTHORNE',5);
define('RACE_SALVENE',6);
define('RACE_THEVIAN',7);
define('RACE_WQHUMAN',8);
define('RACE_NIJARIN',9);

/*
 * Ship types
 */

define('SHIP_TYPE_GALACTIC_SEMI',1);
define('SHIP_TYPE_INTERSTELLAR_TRADER',9);
define('SHIP_TYPE_PLANETARY_SUPER_FREIGHTER',12);
define('SHIP_TYPE_NEWBIE_MERCHANT_VESSEL',28);
define('SHIP_TYPE_SMALL_TIMER',29);
define('SHIP_TYPE_TRIP_MAKER',30);
define('SHIP_TYPE_TRADE_MASTER',33);
define('SHIP_TYPE_MEDIUM_CARGO_HULK',34);
define('SHIP_TYPE_LEVIATHAN',35);
define('SHIP_TYPE_LIGHT_FREIGHTER',39);
define('SHIP_TYPE_AMBASSADOR',40);
define('SHIP_TYPE_RENAISSANCE',41);
define('SHIP_TYPE_TINY_DELIGHT',44);
define('SHIP_TYPE_FAVOURED_OFFSPRING',46);
define('SHIP_TYPE_PROTO_CARRIER',47);
define('SHIP_TYPE_HATCHLINGS_DUE',50);
define('SHIP_TYPE_DRUDGE',51);
define('SHIP_TYPE_SWIFT_VENTURE',56);
define('SHIP_TYPE_EXPEDITER',57);
define('SHIP_TYPE_SLIP_FREIGHTER',62);
define('SHIP_TYPE_ROGUE',65);
define('SHIP_TYPE_BLOCKADE_RUNNER',66);
define('SHIP_TYPE_ESCAPE_POD',69);
define('SHIP_TYPE_REDEEMER',70);
define('SHIP_TYPE_RETALIATION',71);
define('SHIP_TYPE_VENGEANCE',72);

/*
 * Combat system
 */
define('MAX_ATTACK_RATING_NEWBIE',4);
define('MIN_TIME_BETWEEN_SHOTS',.5);
define('MAXIMUM_FLEET_SIZE', 10);
define('MINE_ARMOUR', 20);
define('CD_ARMOUR', 3);
define('SD_ARMOUR', 20);
define('DCS_PLAYER_DAMAGE_DECIMAL_PERCENT', .66);
define('DCS_PORT_DAMAGE_DECIMAL_PERCENT', .75);
define('DCS_PLANET_DAMAGE_DECIMAL_PERCENT', .75);
define('DCS_FORCE_DAMAGE_DECIMAL_PERCENT', .75);
define('WEAPON_PORT_TURRET',10000);
define('WEAPON_PLANET_TURRET',10001);
define('DRONES_BEHIND_SHIELDS_DAMAGE_PERCENT',0.25);

define('PORT_ALLIANCE_ID',0);
define('DEFEND_PORT_BOUNTY_PER_LEVEL',400000);
define('PLANET_GENERATOR',1);
define('PLANET_HANGAR',2);
define('PLANET_TURRET',3);
define('PLANET_GENERATOR_SHIELDS',100);
define('PLANET_HANGAR_DRONES',20);

define('ALIGN_FED_PROTECTION', 0);

/*
 * HoF
 */
define('HOF_PUBLIC', 'PUBLIC');
define('HOF_ALLIANCE', 'ALLIANCE');
define('HOF_PRIVATE', 'PRIVATE');

/*
 * Messaging system
 */
define('MSG_SENT', 0);
define('MSG_GLOBAL', 1);
define('MSG_PLAYER', 2);
define('MSG_PLANET', 3);
define('MSG_SCOUT', 4);
define('MSG_POLITICAL', 5);
define('MSG_ALLIANCE', 6);
define('MSG_ADMIN', 7);
define('BOX_BUGS_AUTO', 1);
define('BOX_BUGS_REPORTED', 2);
define('BOX_GLOBALS', 3);
define('BOX_ALLIANCE_DESCRIPTIONS', 4);
define('BOX_BETA_APPLICATIONS', 5);
define('BOX_ALBUM_COMMENTS', 6);

define('MESSAGE_SCOUT_GROUP_LIMIT',30);

define('COMBAT_LOGS_PER_PAGE',50);
define('MESSAGES_PER_PAGE',50);

/*
 * Credit features
 */

$MESSAGES_PER_CREDIT = array(
	MSG_GLOBAL => 20,
	MSG_PLAYER => 20,
	MSG_PLANET => 10,
	MSG_SCOUT => 25,
	MSG_POLITICAL => 20,
	MSG_ALLIANCE => 20,
	MSG_ADMIN => 50
);

define('CREDITS_PER_NAME_CHANGE',2);

/*
 * Movement
 */
define('DEFAULT_MAX_TURNS', 450);
define('DEFAULT_START_TURNS', 200);
 
define('MOVEMENT_WALK', 1);
define('MOVEMENT_JUMP', 2);
define('MOVEMENT_WARP', 3);

define('EXPLORATION_EXPERIENCE', 2);

define('TURNS_WARP_SECTOR_EQUIVALENCE', 5);
define('TURNS_PER_SECTOR', 1);
define('TURNS_PER_WARP', 5);
define('TURNS_PER_TRADE', 1);

define('TURNS_TO_CLOAK',1);

define('GOOD_NOTHING',0);
/*
 * Special locations
 */
define('GOVERNMENT', 101);
define('UNDERGROUND', 102);
define('FED', 201);
define('RACIAL_SHIPS', 400);
define('RACIAL_SHOPS', 900);
define('RACE_WARS_SHIPS', 512);
define('RACE_WARS_WEAPONS', 326);
define('RACE_WARS_HARDWARE', 607);
define('LOCATION_TYPE_FEDERAL_BEACON', 201);
define('LOCATION_TYPE_FEDERAL_HQ', 101);
define('LOCATION_TYPE_FEDERAL_MINT', 704);
define('LOCATION_GROUP_RACIAL_HQS', 101);
define('LOCATION_GROUP_RACIAL_BEACONS', 201);
define('LOCATION_GROUP_RACIAL_SHIPS', 399);
define('LOCATION_GROUP_RACIAL_SHOPS', 899);

define('DEFAULT_FED_RADIUS',1);

/*
 * Hardware definitions
 */
define('HARDWARE_SHIELDS',1);
define('HARDWARE_ARMOR',2);
define('HARDWARE_ARMOUR',2);
define('HARDWARE_CARGO',3);
define('HARDWARE_COMBAT',4);
define('HARDWARE_SCOUT',5);
define('HARDWARE_MINE',6);
define('HARDWARE_SCANNER',7);
define('HARDWARE_CLOAK',8);
define('HARDWARE_ILLUSION',9);
define('HARDWARE_JUMP',10);
define('HARDWARE_DCS',11);

/*
 * Planet definitions
 */
define('GENERATOR',1);
define('HANGAR',1);
define('TURRET',1);

define('BOND_TIME',172800);

/*
 * Miscellaneous definitions
 */

define('NEWBIE', 1);
define('BEGINNER', 2);
define('FLEDGLING', 3);
define('AVERAGE', 4);

define('PERMISSION_GAME_OPEN_CLOSE', 3);
define('PERMISSION_MODERATE_PHOTO_ALBUM', 20);
define('PERMISSION_MODERATE_FEATURE_REQUEST', 27);
define('PERMISSION_EDIT_ALLIANCE_DESCRIPTION', 28);
define('PERMISSION_EDIT_STARTED_GAMES', 32);

define('UNI_GEN_LOCATION_SLOTS',10);

define('NHA_ID',302);

define('NUM_RACES', 8);

define('MICRO_TIME', microtime());
define('TIME', (int)microtimeSec(MICRO_TIME));
define('INACTIVE_TIME', TIME - 259200);

define('ACCURACY_STAT_FACTOR', 0.04);
define('INCREASED_ACC_GADGET_FACTOR', 0.15);
define('INCREASED_MAN_GADGET_FACTOR', 0.15);
define('MR_FACTOR', 15);
define('INCREASED_DAMAGE_GADGET_FACTOR', .07);
define('WEAPON_DAMAGE_STAT_FACTOR', .025);

define('MIN_RELATIONS', -1000);
define('MIN_EXPERIENCE',0);
define('MAX_EXPERIENCE',4294967296);
define('MAX_COUNCIL_MEMBERS',10);
	
define('NEWBIE_TURNS_WARNING_LIMIT',20);

define('MAX_MONEY',4294967296);

define('EOL',"\n");

define('TEMPLATES_DIR',ROOT . 'templates/');

define('DEFAULT_CSS',URL.'/css/Default.css');
define('DEFAULT_CSS_COLOUR',URL.'/css/Default/Default.css');

define('AJAX_DEFAULT_REFRESH_TIME',1500);
define('AJAX_UNPROTECTED_REFRESH_TIME',800);

define('LOCK_DURATION',10); // The max time for a lock to last before timing out.
define('LOCK_BUFFER',3); // The minimum time that must be remaining on the lock duration for the lock to be valid.

	require_once(LIB . 'Default/Template.class.inc');
	$template = new Template();
	$GLOBALS['template'] =& $template;
//	$template->assign('links',$db->_LINKS);
//	$template->assign('javaScriptFiles',$db->_JS);
	$template->assign('URL',URL);
	$template->assign('CSSLink',DEFAULT_CSS);
	$template->assign('CSSColourLink',DEFAULT_CSS_COLOUR);
	$template->assign('Title','Space Merchant Realms 1.6:');
//	$template->assign('isFirefox',preg_match('/(firefox|minefield)/i',$_SERVER['HTTP_USER_AGENT']));
//	$template->assign('isAprilFools',(date('n') == 4 && date('j') == 1));
	
	$links = array('Register' => 'login_create.php',
					'ResetPassword' => 'resend_password.php');
	$template->assign('Links',$links);
	$template->assign('AJAX_ENABLE_REFRESH',AJAX_DEFAULT_REFRESH_TIME);
?>