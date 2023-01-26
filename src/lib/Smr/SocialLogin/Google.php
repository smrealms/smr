<?php declare(strict_types=1);

namespace Smr\SocialLogin;

use Exception;
use League\OAuth2\Client\Provider\Google as GoogleProvider;
use Smr\Request;

class Google extends SocialLogin {

	public static function getLoginType(): string {
		return 'Google';
	}

	private function getGoogleObj(): GoogleProvider {
		return new GoogleProvider([
			'clientId' => GOOGLE_CLIENT_ID,
			'clientSecret' => GOOGLE_CLIENT_SECRET,
			'redirectUri' => $this->getRedirectUrl(),
		]);
	}

	public function getLoginUrl(): string {
		if (empty(GOOGLE_CLIENT_ID)) {
			// No google api specified. Continuing would throw an exception.
			return URL;
		}
		$provider = $this->getGoogleObj();
		$authUrl = $provider->getAuthorizationUrl([
			'scope' => ['email'],
		]);
		$_SESSION['GoogleToken'] = $provider->getState();
		return $authUrl;
	}

	public function login(): SocialLogin {
		if (!Request::has('code') || !Request::has('state')) {
			// Error response. Return early without validating.
			if (Request::has('error_message')) {
				$this->errorMessage = Request::get('error_message');
			}
			return $this;
		}
		if ($_SESSION['GoogleToken'] != Request::get('state')) {
			throw new Exception('Unexpected token received from Google');
		}
		$provider = $this->getGoogleObj();
		$accessToken = $provider->getAccessToken(
			'authorization_code',
			['code' => Request::get('code')],
		);
		/** @var \League\OAuth2\Client\Provider\GoogleUser $userInfo */
		$userInfo = $provider->getResourceOwner($accessToken);
		$this->setCredentials($userInfo->getId(), $userInfo->getEmail());
		return $this;
	}

}
