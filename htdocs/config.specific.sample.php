<?php
define('USE_COMPATIBILITY',true);

define('URL', 'http://localhost/smr1');
define('ROOT','/home/page/smr/'); 
define('LIB', ROOT . 'lib/');
define('ENGINE', ROOT . 'engine/');
define('WWW', ROOT . 'htdocs/');
define('UPLOAD', '/home/page/wwwWrite/upload/');
define('ADMIN', ROOT . 'admin/');

define('ENABLE_BETA', false);
define('ACCOUNT_PAGE',1403); //BETA, used for removing newbie turn

$COMPATIBILITY_DATABASES = array('Game' => array('Smr12MySqlDatabase'=>array('GameType'=>'1.2','Column'=>'old_account_id')),
													'History' => array('SmrHistoryMySqlDatabase'=>array('GameType'=>'1.2')));
?>