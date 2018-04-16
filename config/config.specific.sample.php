<?php
const USE_COMPATIBILITY = false;

const URL = 'http://localhost';

const ENABLE_DEBUG = true; // This is useful for debugging on dev machines.
const ENABLE_BETA = false;
const ACCOUNT_ID_PAGE = 1403; //BETA, used for removing newbie turn

const RECAPTCHA_PUBLIC = '';
const RECAPTCHA_PRIVATE = '';

const FACEBOOK_APP_ID = '';
const FACEBOOK_APP_SECRET = '';

const TWITTER_CONSUMER_KEY = '';
const TWITTER_CONSUMER_SECRET = '';

const ENABLE_NPCS_CHESS = false;

// Set to empty string if using a local mailserver.
// Use the default value if using the provided docker-compose orchestration.
const SMTP_HOSTNAME = 'smtp';

const COMPATIBILITY_DATABASES = array();
//	array(
//		'Game' => array(
//			'SmrClassicMySqlDatabase' => array(
//				'GameType' => '1.2',
//				'Column' => 'old_account_id'
//			),
//			'Smr12MySqlDatabase' => array(
//				'GameType' => '1.2',
//				'Column' => 'old_account_id2'
//			)
//		),
//		'History' => array(
//			'SmrClassicHistoryMySqlDatabase' => array(
//				'GameType' => '1.2'
//			),
//			'Smr12HistoryMySqlDatabase' => array(
//				'GameType' => '1.2'
//			)
//		)
//	);
