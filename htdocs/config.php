<?php
//error_reporting(E_ALL | E_STRICT);
@date_default_timezone_set(@date_default_timezone_get());
//ini_set('display_errors', TRUE);
//ini_set('display_startup_errors', TRUE);

define('ACCOUNT_PAGE',1403); //BETA

/*
 * FILE: /htdocs/config.php
 * TODO: Transfer all globals into defines in this file
 */
define('IRC_BOT_SOCKET', '/tmp/ircbot.sock');

require_once('config.specific.php');

/*
 * Localisations
 */
define('DATE_FULL_SHORT','j/n/Y g:i:s A');
define('DATE_DATE_SHORT','j/n/Y');
define('DATE_TIME_SHORT','g:i:s A');
define('DATE_FULL_SHORT_SPLIT','j/n/Y\<b\r /\>g:i:s A');
 
 
/*
 * Combat system
 */
define('MAXIMUM_FLEET_SIZE', 10);
define('MINE_ARMOUR', 20);
define('CD_ARMOUR', 3);
define('SD_ARMOUR', 20);
define('DCS_DAMAGE_DECIMAL_PERCENT', 0.75);
define('WEAPON_PORT_TURRET',10000);
define('WEAPON_PLANET_TURRET',10001);
define('DRONES_BEHIND_SHIELDS_DAMAGE_PERCENT',0.25);

define('PORT_ACCOUNT_ID',65535);
define('PORT_ALLIANCE_ID',0);
define('DEFEND_PORT_BOUNTY_PER_LEVEL',400000);
define('PLANET_GENERATOR',1);
define('PLANET_HANGAR',2);
define('PLANET_TURRET',3);
define('PLANET_GENERATOR_SHIELDS',100);
define('PLANET_HANGAR_DRONES',20);


/*
 * Messaging system
 */
define('MSG_GLOBAL', 1);
define('MSG_PLAYER', 2);
define('MSG_PLANET', 3);
define('MSG_SCOUT', 4);
define('MSG_POLITICAL', 5);
define('MSG_ALLIANCE', 6);
define('MSG_ADMIN', 7);

/*
 * Movement types
 */
define('MOVEMENT_WALK', 1);
define('MOVEMENT_JUMP', 2);
define('MOVEMENT_WARP', 3);


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

/*
 * Miscellaneous definitions
 */

define('NEWBIE', 1);
define('BEGINNER', 2);
define('FLEDGLING', 3);
define('AVERAGE', 4);

define('PERMISSION_GAME_OPEN_CLOSE', 3);

define('NUM_RACES', 8);

define('TIME', time());

define('ACCURACY_STAT_FACTOR', 0.04);
define('INCREASED_ACC_GADGET_FACTOR', 0.15);
define('INCREASED_MAN_GADGET_FACTOR', 0.15);
define('MR_FACTOR', 12);
define('INCREASED_DAMAGE_GADGET_FACTOR', .07);
define('WEAPON_DAMAGE_STAT_FACTOR', .025);

define('MIN_EXPERIENCE',0);
define('MAX_EXPERIENCE',4294967296);
define('MAX_COUNCIL_MEMBERS',10);
	
define('NEWBIE_TURNS_WARNING_LIMIT',20);

define('MAX_MONEY',4294967296);

define('EOL',"\n");

define('TEMPLATES_DIR',LIB . 'templates/');

define('DEFAULT_CSS',URL.'/css/default.css');

	
	require_once(LIB . 'Default/Template.class.inc');
	$template = new Template();
	$GLOBALS['smarty'] =& $template;
//	$template->assign('links',$db->_LINKS);
//	$template->assign('javaScriptFiles',$db->_JS);
	$template->assign('URL',URL);
	$template->assign('CSSLink',DEFAULT_CSS);
	$template->assign('Title','Space Merchant Realms 1.6:');
	$template->assign('isFirefox',preg_match('/(firefox|minefield)/i',$_SERVER['HTTP_USER_AGENT']));
	$template->assign('isAprilFools',(date('n') == 4 && date('j') == 1));
	
	$links = array('Register' => 'login_create.php',
					'ResetPassword' => 'resend_password.php');
	$template->assign('Links',$links);
	$template->assign('AJAX_ENABLE_REFRESH',1000);//To help test.
//	$template->assign('AJAX_ENABLE_REFRESH',10000);
?>
