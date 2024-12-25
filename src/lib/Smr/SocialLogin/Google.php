<?php declare(strict_types=1);

namespace Smr\SocialLogin;

use Exception;
use League\OAuth2\Client\Provider\Google as GoogleProvider;
use Smr\Exceptions\UserError;
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
		if (GOOGLE_CLIENT_ID === '') {
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

	public function login(): SocialIdentity {
		if (!Request::has('code') || !Request::has('state')) {
			throw new UserError(Request::get('error_message', ''));
		}
		if ($_SESSION['GoogleToken'] !== Request::get('state')) {
			throw new Exception('Unexpected token received from Google');
		}
		$provider = $this->getGoogleObj();
		$accessToken = $provider->getAccessToken(
			'authorization_code',
			['code' => Request::get('code')],
		);
		/** @var \League\OAuth2\Client\Provider\GoogleUser $userInfo */
		$userInfo = $provider->getResourceOwner($accessToken);
		return new SocialIdentity($userInfo->getId(), $userInfo->getEmail(), static::getLoginType());
	}

}
