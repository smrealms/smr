<?php
define('USE_COMPATIBILITY',false);

define('URL', 'http://localhost/smr1');
define('ROOT','/home/page/smr/');
define('LIB', ROOT . 'lib/');
define('ENGINE', ROOT . 'engine/');
define('WWW', ROOT . 'htdocs/');
define('UPLOAD', '/home/page/wwwWrite/upload/');
define('ADMIN', ROOT . 'admin/');
define('TOOLS', ROOT . 'tools/');

define('ENABLE_BETA',false);
define('ACCOUNT_ID_PAGE',1403); //BETA, used for removing newbie turn

define('RECAPTCHA_PUBLIC','');
define('RECAPTCHA_PRIVATE','');

define('SMS_DEBUG', '1');
define('SMS_GATEWAY_KEY','');

define('FACEBOOK_APP_ID','');
define('FACEBOOK_APP_SECRET','');

define('TWITTER_CONSUMER_KEY','');
define('TWITTER_CONSUMER_SECRET','');

$COMPATIBILITY_DATABASES = array();
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
?>