<?php

namespace SocialLogins;

class Facebook extends \SocialLogin {

	private static $facebook = null;

	public static function getLoginType() : string {
		return 'Facebook';
	}

	private static function getFacebookObj() : \Facebook\Facebook {
		if (is_null(self::$facebook)) {
			self::$facebook = new \Facebook\Facebook([
				'app_id' => FACEBOOK_APP_ID,
				'app_secret' => FACEBOOK_APP_SECRET,
				'default_graph_version' => 'v2.12'
			]);
		}
		return self::$facebook;
	}

	public function getLoginUrl() : string {
		if (empty(FACEBOOK_APP_ID)) {
			// No facebook app specified. Continuing would throw an exception.
			return URL;
		}
		$helper = self::getFacebookObj()->getRedirectLoginHelper();
		$permissions = ['email'];
		return $helper->GetLoginUrl($this->getRedirectUrl(), $permissions);
	}

	public function login() : \SocialLogin {
		$helper = self::getFacebookObj()->getRedirectLoginHelper();
		$accessToken = $helper->getAccessToken($this->getRedirectUrl());
		$response = self::getFacebookObj()->get('/me?fields=email', $accessToken);
		$userInfo = $response->getGraphUser();
		$this->setCredentials($userInfo->getId(), $userInfo->getEmail());
		return $this;
	}

}
