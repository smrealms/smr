<?php
require_once('socialConfig.specific.inc');
class SocialLogin
{
	private $loginType = null;
	private $userID = null;
	private $email = null;
	private $firstName = null;
	private $lastName = null;
	private static $facebook=null;
	
	private static function &getFacebookObj()
	{
		if(self::$facebook==null)
		{
			require_once(LIB.'External/Facebook/facebook.php');
			self::$facebook = new Facebook(array(
				'appId'  => FACEBOOK_APP_ID,
				'secret' => FACEBOOK_APP_SECRET,
				'cookie' => true,
			));
		}
		return self::$facebook;
	}
	
	public static function getFacebookLoginUrl()
	{
		return self::getFacebookObj()->getLoginUrl(array('fbconnect'=>null,'next'=>URL.'/login_processing.php?loginType=Facebook','cancel_url'=>URL.'/login_processing.php?loginType=Facebook','req_perms'=>'email'));
	}
	
	public function __construct($loginType)
	{
	
		if($loginType=='Facebook')
		{
			if(self::getFacebookObj()->getSession())
			{
				$this->loginType=$loginType;
				$this->userID=self::getFacebookObj()->getUser();
				$userInfo = self::getFacebookObj()->api('me');
				$this->email=$userInfo['email'];
				$this->firstName=$userInfo['first_name'];
				$this->lastName=$userInfo['last_name'];
				return true;
			}
		}
		return false;
	}
	
	public function getLoginType()
	{
		return $this->loginType;
	}
	
	public function getUserID()
	{
		return $this->userID;
	}
	
	public function getEmail()
	{
		return $this->email;
	}
}

?>