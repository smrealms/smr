<?php

//-------------------
// Main
//-------------------

const URL = 'http://localhost';

const ENABLE_LIBXML_ERRORS = true; // Convert libXML warnings into errors.
const ENABLE_DEBUG = true; // This is useful for debugging on dev machines.
const ENABLE_BETA = false;

const RECAPTCHA_PUBLIC = '';
const RECAPTCHA_PRIVATE = '';

const FACEBOOK_APP_ID = '';
const FACEBOOK_APP_SECRET = '';

const TWITTER_CONSUMER_KEY = '';
const TWITTER_CONSUMER_SECRET = '';

const GOOGLE_CLIENT_ID = '';
const GOOGLE_CLIENT_SECRET = '';

const GOOGLE_ANALYTICS_ID = '';

// Set to empty string if using a local mailserver.
// Use the default value if using the provided docker-compose orchestration.
const SMTP_HOSTNAME = 'smtp';

// E-mail addresses to receive bug reports
const BUG_REPORT_TO_ADDRESSES = [];

//const HISTORY_DATABASES = [
//	'smr_classic_history' => 'old_account_id',
//	'smr_12_history' => 'old_account_id2',
//];

//-------------------
// Discord
//-------------------

const DISCORD_TOKEN = 'YOUR_TOKEN_HERE';
const DISCORD_COMMAND_PREFIX = '.';
const DISCORD_LOGGER_LEVEL = 'INFO';

//-------------------
// IRC
//-------------------

const IRC_BOT_SERVER_ADDRESS = 'irc.theairlock.net';
const IRC_BOT_SERVER_PORT = 6667;
const IRC_BOT_NICK = 'Caretaker';
const IRC_BOT_PASS = 'secret_key';
const IRC_BOT_USER = IRC_BOT_NICK . ' oberon smrealms.de :Test SMR bot';
const IRC_BOT_VERBOSE_PING = false;

//-------------------
// NPC
//-------------------

const NPC_LOG_TO_DATABASE = true; // insert debug messages into db
const NPC_MAX_ACTIONS = 2500; // About a half hour worth of actions
const NPC_LOW_TURNS = 75;
const NPC_MINIMUM_RESERVE_CREDITS = 100000;
const NPC_MIN_SLEEP_TIME = 800000;
const NPC_MAX_SLEEP_TIME = 1100000;

//-------------------
// NPC Chess
//-------------------

const ENABLE_NPCS_CHESS = false;
const UCI_CHESS_ENGINE = '/path/to/UCI_CHESS_ENGINE'; // Stockfish works: http://www.stockfishchess.com
const UCI_TIME_PER_MOVE_MS = 2000;
const UCI_SLEEP_BETWEEN_CYCLES_US = 2000000;
const UCI_HASH_SIZE_MB = 512;
