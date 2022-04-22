<?php declare(strict_types=1);

namespace Smr\SocialLogin;

use Exception;
use League\OAuth2\Client\Provider\Facebook as FacebookProvider;
use Smr\Request;

class Facebook extends SocialLogin {

	public static function getLoginType(): string {
		return 'Facebook';
	}

	private function getFacebookObj(): FacebookProvider {
		return new FacebookProvider([
			'clientId' => FACEBOOK_APP_ID,
			'clientSecret' => FACEBOOK_APP_SECRET,
			'redirectUri' => $this->getRedirectUrl(),
			'graphApiVersion' => 'v11.0',
		]);
	}

	public function getLoginUrl(): string {
		if (empty(FACEBOOK_APP_ID)) {
			// No facebook app specified. Continuing would throw an exception.
			return URL;
		}
		$provider = $this->getFacebookObj();
		$authUrl = $provider->getAuthorizationUrl([
			'scope' => ['email'],
		]);
		$_SESSION['FacebookToken'] = $provider->getState();
		return $authUrl;
	}

	public function login(): SocialLogin {
		if ($_SESSION['FacebookToken'] != Request::get('state')) {
			throw new Exception('Unexpected token received from Facebook');
		}
		if (!Request::has('code')) {
			// User may have rejected permissions? Do not validate.
			return $this;
		}
		$provider = $this->getFacebookObj();
		$accessToken = $provider->getAccessToken(
			'authorization_code',
			['code' => Request::get('code')],
		);
		$userInfo = $provider->getResourceOwner($accessToken);
		$this->setCredentials($userInfo->getId(), $userInfo->getEmail());
		return $this;
	}

}
