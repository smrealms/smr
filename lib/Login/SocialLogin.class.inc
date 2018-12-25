<?php

class SocialLogin {
	// Define `loginType` constants to avoid string typos
	const FACEBOOK = 'Facebook';
	const TWITTER = 'Twitter';
	const GOOGLE = 'Google';

	private $valid = false;
	private $loginType = null;
	private $userID = null;
	private $email = null;
	private static $facebook=null;
	private static $openID=null;
	private static $OPEN_ID_IDENTITIES = array();

	private static function getFacebookObj() {
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}
		if(self::$facebook==null) {
			self::$facebook = new Facebook\Facebook([
				'app_id' => FACEBOOK_APP_ID,
				'app_secret' => FACEBOOK_APP_SECRET,
				'default_graph_version' => 'v2.12'
			]);
		}
		return self::$facebook;
	}

	private static function getTwitterObj($token=null) {
		return new Abraham\TwitterOAuth\TwitterOAuth(
			TWITTER_CONSUMER_KEY,
			TWITTER_CONSUMER_SECRET,
			is_null($token) ? null : $token['oauth_token'],
			is_null($token) ? null : $token['oauth_token_secret']
		);
	}

	private static function getOpenIdObj() {
		if(self::$openID==null) {
			require_once(LIB.'External/OpenID/openid.php');
			self::$openID = new LightOpenID();
		}
		return self::$openID;
	}

	/**
	 * Returns the URL that the social platform will redirect to
	 * after authentication.
	 */
	private static function getRedirectUrl($loginType) {
		return URL . '/login_processing.php?loginType=' . $loginType;
	}

	public static function getFacebookLoginUrl() {
		if (empty(FACEBOOK_APP_ID)) {
			// No facebook app specified. Continuing would throw an exception.
			return;
		}
		$helper = self::getFacebookObj()->getRedirectLoginHelper();
		$permissions = ['email'];
		return $helper->GetLoginUrl(self::getRedirectUrl(self::FACEBOOK), $permissions);
	}

	public static function getTwitterLoginUrl() {
		if (empty(TWITTER_CONSUMER_KEY)) {
			// No twitter app specified. Continuing would throw an exception.
			return;
		}
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}
		$auth = self::getTwitterObj();
		$params = ['oauth_callback' => self::getRedirectUrl(self::TWITTER)];
		$_SESSION['TwitterToken'] = $auth->oauth('oauth/request_token', $params);
		// 'authenticate' asks for permission only once ('authorize' is every time)
		return $auth->url('oauth/authenticate', $_SESSION['TwitterToken']);
	}

	public static function getOpenIdLoginUrl($loginType) {
		if(!isset(self::$OPEN_ID_IDENTITIES[$loginType])) {
			throw new Exception('OpenID does not exist for login type: '.$loginType);
		}
		$openID = self::getOpenIdObj();
		$openID->required = array('contact/email');
		$openID->returnUrl = self::getRedirectUrl($loginType);
		$openID->identity = self::$OPEN_ID_IDENTITIES[$loginType];
		return $openID->authUrl();
	}

	public function __construct($loginType) {
		$this->loginType = $loginType;

		if($loginType == self::FACEBOOK) {
			$helper = self::getFacebookObj()->getRedirectLoginHelper();
			$accessToken = $helper->getAccessToken(self::getRedirectUrl($loginType));
			$response = self::getFacebookObj()->get('/me?fields=email', $accessToken);
			$userInfo = $response->getGraphUser();
			$this->userID = $userInfo->getId();
			$this->email = $userInfo->getEmail();
			$this->valid = true;
		}
		else if($loginType == self::TWITTER) {
			if (session_status() === PHP_SESSION_NONE) {
				session_start();
			}
			if ($_SESSION['TwitterToken']['oauth_token'] != $_REQUEST['oauth_token']) {
				create_error('Unexpected token received from Twitter');
			}
			$helper = self::getTwitterObj($_SESSION['TwitterToken']);
			$accessToken = $helper->oauth('oauth/access_token',
			                              ['oauth_verifier' => $_REQUEST['oauth_verifier']]);
			$auth = self::getTwitterObj($accessToken);
			$userInfo = $auth->get('account/verify_credentials', ['include_email' => 'true']);
			if ($auth->getLastHttpCode() == 200) {
				$this->userID = $userInfo->id_str;
				$this->email = $userInfo->email;
				$this->valid = true;
			}
		}
		else if($loginType == self::GOOGLE) {
			if(self::getOpenIdObj()->validate()) {
				if(strripos(self::getOpenIdObj()->identity, self::$OPEN_ID_IDENTITIES[$loginType]) === 0) {
					$this->userID=self::getOpenIdObj()->identity;
					$userInfo = self::getOpenIdObj()->getAttributes();
					$this->email=$userInfo['contact/email'];
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
