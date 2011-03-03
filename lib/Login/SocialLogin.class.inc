<?php
require_once('socialConfig.specific.inc');
class SocialLogin
{
	private static $loginType = null;
	private static $userID = null;
	private static $facebook=null;
	private static function &getFacebookObj()
	{
		if($facebook==null)
		{
			require_once('lib/facebook.php');
			$facebook = new Facebook(array(
				'appId'  => FACEBOOK_APP_ID,
				'secret' => FACEBOOK_APP_SECRET,
				'cookie' => true,
			));
		}
		return $facebook;
	}
	
	public static function getFacebookLoginUrl()
	{
		return self::getFacebookObj()->getLoginUrl(array('fbconnect'=>null,'next'=>URL.'/login_processing.php?loginType=fb','cancel_url'=>URL.'/login_processing.php?loginType=fb'));
	}
	
	public static function checkLogin()
	{
		if($_REQUEST['loginType']=='Facebook')
		{
			if(self::getFacebookObj()->getSession())
			{
				self::$loginType='Facebook';
				self::$userID=self::getFacebookObj()->getUser();
				return true;
			}
			return false;
		}
	}
	
	public static function getLoginType()
	{
		return self::$loginType;
	}
	
	public static function getUserID()
	{
		return self::$userID;
	}
}

?>