<?php
require_once('socialConfig.specific.inc');
class SocialLogin
{
	private $valid = false;
	private $loginType = null;
	private $userID = null;
	private $email = null;
	private $firstName = null;
	private $lastName = null;
	private static $facebook=null;
	private static $openID=null;
	
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
	
	public static function getOpenIdObj()
	{
		if(self::$openID==null)
		{
			require_once(LIB.'External/OpenID/openid.php');
			self::$openID = new LightOpenID();
		}
		return self::$openID;
		if(!$openID->mode)
		{
		}
		return $openID;
	}
	
	public static function getFacebookLoginUrl()
	{
		return self::getFacebookObj()->getLoginUrl(array('fbconnect'=>null,'next'=>URL.'/login_processing.php?loginType=Facebook','cancel_url'=>URL.'/login_processing.php?loginType=Facebook','req_perms'=>'email'));
	}
	
	public static function getGoogleLoginUrl()
	{
		$openID =& self::getOpenIdObj();
		$openID->required = array('contact/email','namePerson/first','namePerson/last');
		$openID->returnUrl = URL.'/login_processing.php?loginType=Google';
		$openID->identity = 'https://www.google.com/accounts/o8/id';
		return $openID->authUrl();
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
				$this->valid = true;
			}
		}
		else if($loginType == 'Google')
		{
			if(self::getOpenIdObj()->validate())
			{
				$this->loginType=$loginType;
				$this->userID=self::getOpenIdObj()->identity;
				$userInfo = self::getOpenIdObj()->getAttributes();
				$this->email=$userInfo['contact/email'];
				$this->firstName=$userInfo['namePerson/first'];
				$this->lastName=$userInfo['namePerson/last'];
				$this->valid = true;
			}
		}
	}
	
	public function isValid()
	{
		return $this->valid;
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