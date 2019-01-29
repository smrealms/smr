<?php
ini_set('date.timezone', 'UTC');

define('MICRO_TIME', microtime());
define('TIME', (int)microtimeSec(MICRO_TIME));

// Repository paths
const ROOT =  __DIR__ . '/../';
const LIB = ROOT.'lib/';
const ENGINE = ROOT.'engine/';
const WWW = ROOT.'htdocs/';
const UPLOAD = WWW.'upload/';
const ADMIN = ROOT.'admin/';
const TOOLS = ROOT.'tools/';
const CONFIG = ROOT.'config/';
const TEMPLATES_DIR = ROOT . 'templates/';

// Define server-specific constants
require_once(CONFIG . 'config.specific.php');

if (ENABLE_DEBUG) {
	// Warn about everything when in debug mode
	error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
}

const ACCOUNT_ID_PORT = 65535;
const ACCOUNT_ID_ADMIN = 65534;
const ACCOUNT_ID_PLANET = 65533;
const ACCOUNT_ID_ALLIANCE_AMBASSADOR = 65532;
const ACCOUNT_ID_CASINO = 65531;
const ACCOUNT_ID_BANK_REPORTER = 65530;
const ACCOUNT_ID_FED_CLERK = 65529;
const ACCOUNT_ID_OP_ANNOUNCE = 65528;
const ACCOUNT_ID_ALLIANCE_COMMAND = 65527;
const ACCOUNT_ID_GROUP_RACES = 65500;
const ACCOUNT_ID_NHL = 36;

const TIME_BETWEEN_VOTING = 84600; //23 1/2 hours
const TIME_BEFORE_NEWBIE_TIME = 3600; //1 hour
const TIME_FOR_COUNCIL_VOTE = 172800; //2 days
const TIME_FOR_WAR_VOTE_FED_SAFETY = 259200; //3 days
const TIME_MAP_BUY_WAIT = 259200; //3 days
const TIME_FOR_BREAKING_NEWS = 86400; //1 day
const VOTE_BONUS_TURNS_TIME = 1800; //30 mins
const BOND_TIME = 172800; //48 hours
const TIME_LOTTO = 172800; //2 days

const MAX_IMAGE_SIZE = 30; //in kb
const MAX_IMAGE_WIDTH = 200;
const MAX_IMAGE_HEIGHT = 30;

const MULTI_CHECKING_COOKIE_VERSION = 'v3';

const WIKI_URL = 'http://wiki.smrealms.de';

/*
 * Localisations
 */
const DEFAULT_DATE_DATE_SHORT = 'j/n/Y';
const DEFAULT_DATE_TIME_SHORT = 'g:i:s A';
const DEFAULT_DATE_FULL_SHORT = DEFAULT_DATE_DATE_SHORT.' '.DEFAULT_DATE_TIME_SHORT;
const DEFAULT_DATE_FULL_SHORT_SPLIT = DEFAULT_DATE_DATE_SHORT.'\<b\r /\>'.DEFAULT_DATE_TIME_SHORT;
const DEFAULT_DATE_FULL_LONG = 'l jS F '.DEFAULT_DATE_TIME_SHORT;

/*
 * Buyer restrictions for ships and weapons
 */
const BUYER_RESTRICTION_GOOD = 1;
const BUYER_RESTRICTION_EVIL = 2;
const BUYER_RESTRICTION_NEWBIE = 3;
const BUYER_RESTRICTION_PORT = 4;
const BUYER_RESTRICTION_PLANET = 5;

/*
 * Alignment
 */
const ALIGNMENT_GOOD = 100;
const ALIGNMENT_EVIL = -100;
const ALIGNMENT_PRESIDENT = 150;

/*
 * Log types
 */
const LOG_TYPE_LOGIN = 1;
const LOG_TYPE_GAME_ENTERING = 2;
const LOG_TYPE_ALLIANCE = 3;
const LOG_TYPE_BANK = 4;
const LOG_TYPE_MOVEMENT = 5;
const LOG_TYPE_TRADING = 6;
const LOG_TYPE_PORT_RAIDING = 7;
const LOG_TYPE_TRADER_COMBAT = 8;
const LOG_TYPE_FORCES = 9;
const LOG_TYPE_HARDWARE = 10;
const LOG_TYPE_PLANETS = 11;
const LOG_TYPE_PLANET_BUSTING = 12;
const LOG_TYPE_ACCOUNT_CHANGES = 13;

/*
 * Race types
 */

const RACE_NEUTRAL = 1;
const RACE_ALSKANT = 2;
const RACE_CREONTI = 3;
const RACE_HUMAN = 4;
const RACE_IKTHORNE = 5;
const RACE_SALVENE = 6;
const RACE_THEVIAN = 7;
const RACE_WQHUMAN = 8;
const RACE_NIJARIN = 9;

/*
 * Trade goods
 */
const GOODS_WOOD = 1;
const GOODS_FOOD = 2;
const GOODS_ORE = 3;
const GOODS_PRECIOUS_METALS = 4;
const GOODS_SLAVES = 5;
const GOODS_TEXTILES = 6;
const GOODS_MACHINERY = 7;
const GOODS_CIRCUITRY = 8;
const GOODS_WEAPONS = 9;
const GOODS_COMPUTERS = 10;
const GOODS_LUXURY_ITEMS = 11;
const GOODS_NARCOTICS = 12;

/*
 * Ship types
 */

const SHIP_TYPE_GALACTIC_SEMI = 1;
const SHIP_TYPE_INTERSTELLAR_TRADER = 9;
const SHIP_TYPE_PLANETARY_SUPER_FREIGHTER = 12;
const SHIP_TYPE_FEDERAL_ULTIMATUM = 22;
const SHIP_TYPE_DEATH_CRUISER = 25;
const SHIP_TYPE_NEWBIE_MERCHANT_VESSEL = 28;
const SHIP_TYPE_SMALL_TIMER = 29;
const SHIP_TYPE_TRIP_MAKER = 30;
const SHIP_TYPE_TRADE_MASTER = 33;
const SHIP_TYPE_MEDIUM_CARGO_HULK = 34;
const SHIP_TYPE_LEVIATHAN = 35;
const SHIP_TYPE_DEVASTATOR = 38;
const SHIP_TYPE_LIGHT_FREIGHTER = 39;
const SHIP_TYPE_AMBASSADOR = 40;
const SHIP_TYPE_RENAISSANCE = 41;
const SHIP_TYPE_DESTROYER = 43;
const SHIP_TYPE_TINY_DELIGHT = 44;
const SHIP_TYPE_FAVOURED_OFFSPRING = 46;
const SHIP_TYPE_PROTO_CARRIER = 47;
const SHIP_TYPE_MOTHER_SHIP = 49;
const SHIP_TYPE_HATCHLINGS_DUE = 50;
const SHIP_TYPE_DRUDGE = 51;
const SHIP_TYPE_EATER_OF_SOULS = 55;
const SHIP_TYPE_SWIFT_VENTURE = 56;
const SHIP_TYPE_EXPEDITER = 57;
const SHIP_TYPE_ASSAULT_CRAFT = 61;
const SHIP_TYPE_SLIP_FREIGHTER = 62;
const SHIP_TYPE_NEGOTIATOR = 63;
const SHIP_TYPE_ROGUE = 65;
const SHIP_TYPE_BLOCKADE_RUNNER = 66;
const SHIP_TYPE_DARK_MIRAGE = 67;
const SHIP_TYPE_ESCAPE_POD = 69;
const SHIP_TYPE_REDEEMER = 70;
const SHIP_TYPE_RETALIATION = 71;
const SHIP_TYPE_VENGEANCE = 72;
const SHIP_TYPE_FURY = 75;

/*
 * Combat log pages
 */
const COMBAT_LOG_PERSONAL = 0;
const COMBAT_LOG_ALLIANCE = 1;
const COMBAT_LOG_FORCE = 2;
const COMBAT_LOG_PORT = 3;
const COMBAT_LOG_PLANET = 4;
const COMBAT_LOG_SAVED = 5;

/*
 * Combat system
 */
const MAX_ATTACK_RATING_NEWBIE = 4;
const MAXIMUM_PVP_FLEET_SIZE = 10;
const MAXIMUM_PORT_FLEET_SIZE = 10;
const MAXIMUM_PLANET_FLEET_SIZE = 10;
const MINE_ARMOUR = 20;
const CD_ARMOUR = 3;
const SD_ARMOUR = 20;
const DCS_PLAYER_DAMAGE_DECIMAL_PERCENT = .66;
const DCS_PORT_DAMAGE_DECIMAL_PERCENT = .75;
const DCS_PLANET_DAMAGE_DECIMAL_PERCENT = .75;
const DCS_FORCE_DAMAGE_DECIMAL_PERCENT = .75;
const WEAPON_PORT_TURRET = 10000;
const WEAPON_PLANET_TURRET = 10001;
const DRONES_BEHIND_SHIELDS_DAMAGE_PERCENT = 0.25;

const PORT_ALLIANCE_ID = 0;
const DEFEND_PORT_BOUNTY_PER_LEVEL = 1000000;
const PLANET_GENERATOR = 1;
const PLANET_HANGAR = 2;
const PLANET_TURRET = 3;
const PLANET_BUNKER = 4;
const PLANET_WEAPON_MOUNT = 5;
const PLANET_RADAR = 6;
const PLANET_GENERATOR_SHIELDS = 100;
const PLANET_HANGAR_DRONES = 20;
const PLANET_BUNKER_ARMOUR = 100;

const ALIGN_FED_PROTECTION = 0;

/*
 * Relations
 */
const MAX_GLOBAL_RELATIONS = 500;
const MIN_GLOBAL_RELATIONS = -500;
const MIN_RELATIONS = -1000;
const RELATIONS_WAR = -300;
const RELATIONS_PEACE = 300;
const RELATIONS_VOTE_CHANGE = 15;

/*
 * HoF
 */
const HOF_PUBLIC = 'PUBLIC';
const HOF_ALLIANCE = 'ALLIANCE';
const HOF_PRIVATE = 'PRIVATE';

/*
 * Messaging system
 */
const MSG_SENT = 0;
const MSG_GLOBAL = 1;
const MSG_PLAYER = 2;
const MSG_PLANET = 3;
const MSG_SCOUT = 4;
const MSG_POLITICAL = 5;
const MSG_ALLIANCE = 6;
const MSG_ADMIN = 7;
const MSG_CASINO = 8;

const BOX_BUGS_AUTO = 1;
const BOX_BUGS_REPORTED = 2;
const BOX_GLOBALS = 3;
const BOX_ALLIANCE_DESCRIPTIONS = 4;
const BOX_ALBUM_COMMENTS = 6;
const BOX_BARTENDER = 7;

const MESSAGE_SCOUT_GROUP_LIMIT = 30;

const COMBAT_LOGS_PER_PAGE = 50;
const MESSAGES_PER_PAGE = 50;

/*
 * Credit features
 */

const MESSAGES_PER_CREDIT = array(
	MSG_GLOBAL => 20,
	MSG_PLAYER => 20,
	MSG_PLANET => 10,
	MSG_SCOUT => 25,
	MSG_POLITICAL => 20,
	MSG_ALLIANCE => 20,
	MSG_ADMIN => 50
);

const CREDITS_PER_GAL_MAP = 20;
const CREDITS_PER_NAME_CHANGE = 10;
const CREDITS_PER_TICKER = 10;
const CREDITS_PER_TEXT_SHIP_NAME = 10;
const CREDITS_PER_HTML_SHIP_NAME = 20;
const CREDITS_PER_SHIP_LOGO = 30;
const CREDITS_PER_DOLLAR = 10;

/*
 * Movement
 */
const DEFAULT_MAX_TURNS = 450;
const DEFAULT_START_TURN_HOURS = 15;

const MOVEMENT_WALK = 1;
const MOVEMENT_JUMP = 2;
const MOVEMENT_WARP = 3;

const EXPLORATION_EXPERIENCE = 2;

const TURNS_WARP_SECTOR_EQUIVALENCE = 5;
const TURNS_PER_SECTOR = 1;
const TURNS_PER_WARP = 5;
const TURNS_PER_TRADE = 1;
const TURNS_PER_JUMP_DISTANCE = .65;
const MISJUMP_LEVEL_FACTOR = .02;
const MISJUMP_DISTANCE_DIFF_FACTOR = 1.2;
const TURNS_JUMP_MINIMUM = 10;

const TURNS_TO_CLOAK = 1;
const TURNS_TO_SHOOT_PORT = 2;

const GOOD_NOTHING = 0;
/*
 * Special locations
 */
const GOVERNMENT = 101;
const UNDERGROUND = 102;
const FED = 201;
const RACIAL_SHIPS = 400;
const RACIAL_SHOPS = 900;
const RACE_WARS_SHIPS = 512;
const RACE_WARS_WEAPONS = 326;
const RACE_WARS_HARDWARE = 607;
const LOCATION_TYPE_FEDERAL_BEACON = 201;
const LOCATION_TYPE_FEDERAL_HQ = 101;
const LOCATION_TYPE_FEDERAL_MINT = 704;
const LOCATION_GROUP_RACIAL_HQS = 101;
const LOCATION_GROUP_RACIAL_BEACONS = 201;
const LOCATION_GROUP_RACIAL_SHIPS = 399;
const LOCATION_GROUP_RACIAL_SHOPS = 899;

const DEFAULT_FED_RADIUS = 1;

/*
 * Hardware definitions
 */
const HARDWARE_SHIELDS = 1;
const HARDWARE_ARMOR = 2;
const HARDWARE_ARMOUR = 2;
const HARDWARE_CARGO = 3;
const HARDWARE_COMBAT = 4;
const HARDWARE_SCOUT = 5;
const HARDWARE_MINE = 6;
const HARDWARE_SCANNER = 7;
const HARDWARE_CLOAK = 8;
const HARDWARE_ILLUSION = 9;
const HARDWARE_JUMP = 10;
const HARDWARE_DCS = 11;

/*
 * Special Alliance vs. Alliance categories
 */
const ALLIANCE_VS_FORCES = -1;
const ALLIANCE_VS_PLANETS = -2;
const ALLIANCE_VS_PORTS = -3;

/*
 * Miscellaneous definitions
 */

const STARTING_NEWBIE_TURNS_NEWBIE = 750;
const STARTING_NEWBIE_TURNS_VET = 250;

const NEWBIE = 1;
const BEGINNER = 2;
const FLEDGLING = 3;
const AVERAGE = 4;

const PERMISSION_GAME_OPEN_CLOSE = 3;
const PERMISSION_MODERATE_PHOTO_ALBUM = 20;
const PERMISSION_MODERATE_FEATURE_REQUEST = 27;
const PERMISSION_EDIT_ALLIANCE_DESCRIPTION = 28;
const PERMISSION_EDIT_STARTED_GAMES = 32;
const PERMISSION_DISPLAY_ADMIN_TAG = 36;

const ALLIANCE_BANK_UNLIMITED = -1;

const UNI_GEN_LOCATION_SLOTS = 9;

const NHA_ID = 302;

const TIME_BEFORE_INACTIVE = 259200; // 3 days.

const CLOSE_ACCOUNT_BY_REQUEST_REASON = 'User Request';
const CLOSE_ACCOUNT_INVALID_EMAIL_REASON = 'Invalid email';

const INCREASED_ACC_GADGET_FACTOR = 0.15;
const INCREASED_MAN_GADGET_FACTOR = 0.15;
const MR_FACTOR = 15;
const INCREASED_DAMAGE_GADGET_FACTOR = .07;

const MIN_EXPERIENCE = 0;
const MAX_EXPERIENCE = 4294967296;
const MAX_COUNCIL_MEMBERS = 5;

const NEWBIE_TURNS_WARNING_LIMIT = 20;

const MAX_MONEY = 4294967296;
const SHIP_REFUND_PERCENT = .75;
const WEAPON_REFUND_PERCENT = .5;
const CDS_REFUND_PERCENT = .5;

const EOL = "\n";

// These CSS URLs must be hard-coded here so that grunt-cache-bust
// can replace them with the hashed filenames.
const CSS_URLS = array(
	'Default' => 'css/Default.css',
	'Freon22' => 'css/Freon22.css',
);
const CSS_COLOUR_URLS = array(
	'Default' => array(
		'Default' => 'css/Default/Default.css',
	),
	'Freon22' => array(
		'Default'      => 'css/Freon22/Default.css',
		'Blue'         => 'css/Freon22/Blue.css',
		'ClassicGreen' => 'css/Freon22/ClassicGreen.css',
		'None'         => 'css/Freon22/None.css',
	),
);

const DEFAULT_CSS = CSS_URLS['Default'];
const DEFAULT_CSS_COLOUR = CSS_COLOUR_URLS['Default']['Default'];

const AJAX_DEFAULT_REFRESH_TIME = 1500;
const AJAX_UNPROTECTED_REFRESH_TIME = 800;

const LOCK_DURATION = 10; // The max time for a lock to last before timing out.
const LOCK_BUFFER = 3; // The minimum time that must be remaining on the lock duration for the lock to be valid.

define('USING_AJAX',isset($_REQUEST['ajax'])&&$_REQUEST['ajax']==1);
