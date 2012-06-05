<?php
class SocialLogin {
	private $valid = false;
	private $loginType = null;
	private $userID = null;
	private $email = null;
	private $firstName = null;
	private $lastName = null;
	private static $facebook=null;
	private static $twitter=null;
	private static $openID=null;
	private static $OPEN_ID_IDENTITIES = array('Google' => 'https://www.google.com/accounts/o8/id');

	private static function &getFacebookObj() {
		if(self::$facebook==null) {
			require_once(LIB.'External/Facebook/facebook.php');
			self::$facebook = new Facebook(array(
				'appId' => FACEBOOK_APP_ID,
				'secret' => FACEBOOK_APP_SECRET,
				'cookie' => true,
			));
		}
		return self::$facebook;
	}

	private static function &getTwitterObj() {
		if(self::$twitter==null) {
			if(!isset($_SESSION)) {
				session_start();
			}
			require_once(LIB.'External/Twitter/twitteroauth.php');
			self::$twitter = new TwitterOAuth(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET, isset($_SESSION['TwitterToken']['oauth_token'])?$_SESSION['TwitterToken']['oauth_token']:null, isset($_SESSION['TwitterToken']['oauth_token_secret'])?$_SESSION['TwitterToken']['oauth_token_secret']:null);
		}
		return self::$twitter;
	}

	public static function &getOpenIdObj() {
		if(self::$openID==null) {
			require_once(LIB.'External/OpenID/openid.php');
			self::$openID = new LightOpenID();
		}
		return self::$openID;
	}

	public static function getFacebookLoginUrl() {
		return self::getFacebookObj()->getLoginUrl(array('redirect_uri'=>URL.'/login_processing.php?loginType=Facebook','scope'=>'email'));
	}

	public static function getTwitterLoginUrl() {
		if(!isset($_SESSION)) {
			session_start();
		}
		self::getTwitterObj()->token = null;
		$_SESSION['TwitterToken'] = self::getTwitterObj()->getRequestToken(URL.'/login_processing.php?loginType=Twitter');
		return self::getTwitterObj()->getAuthorizeURL($_SESSION['TwitterToken']);
	}

	public static function getOpenIdLoginUrl($loginType) {
		if(!isset(self::$OPEN_ID_IDENTITIES[$loginType])) {
			throw new Exception('OpenID does not exist for login type: '.$loginType);
		}
		$openID =& self::getOpenIdObj();
		$openID->required = array('contact/email','namePerson/first','namePerson/last');
		$openID->returnUrl = URL.'/login_processing.php?loginType='.$loginType;
		$openID->identity = self::$OPEN_ID_IDENTITIES[$loginType];
		return $openID->authUrl();
	}

	public function __construct($loginType) {
		if($loginType=='Facebook') {
			if($this->userID=self::getFacebookObj()->getUser()) {
				$this->loginType=$loginType;
				$userInfo = self::getFacebookObj()->api('me');
				$this->email=$userInfo['email'];
				$this->firstName=$userInfo['first_name'];
				$this->lastName=$userInfo['last_name'];
				$this->valid = true;
			}
		}
		else if($loginType == 'Twitter') {
			self::getTwitterObj()->getAccessToken($_REQUEST['oauth_verifier']);
			if(self::getTwitterObj()->http_code == 200) {
				$this->loginType=$loginType;

				$userInfo = self::getTwitterObj()->get('account/verify_credentials');
				$this->userID=$userInfo->id_str;
				$spaceIndex = strpos($userInfo->name, ' ');
				if($spaceIndex===false) {
					$this->firstName=$userInfo->name;
				}
				else {
					$this->firstName=substr($userInfo->name, 0, $spaceIndex);
					$this->lastName=substr($userInfo->name, $spaceIndex);
				}
				$this->valid = true;
			}
		}
		else if($loginType == 'Google') {
			if(self::getOpenIdObj()->validate()) {
				if(strripos(self::getOpenIdObj()->identity, self::$OPEN_ID_IDENTITIES[$loginType]) === 0) {
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
	}

	public function isValid() {
		return $this->valid;
	}

	public function getLoginType() {
		return $this->loginType;
	}

	public function getUserID() {
		return $this->userID;
	}

	public function getEmail() {
		return $this->email;
	}
}

?>