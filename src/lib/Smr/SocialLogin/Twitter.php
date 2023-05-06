<?php declare(strict_types=1);

namespace Smr\SocialLogin;

use Abraham\TwitterOAuth\TwitterOAuth;
use Exception;
use Smr\Exceptions\UserError;
use Smr\Request;

class Twitter extends SocialLogin {

	public static function getLoginType(): string {
		return 'Twitter';
	}

	/**
	 * @param ?array<string, string> $token
	 */
	private static function getTwitterObj(?array $token = null): TwitterOAuth {
		return new TwitterOAuth(
			TWITTER_CONSUMER_KEY,
			TWITTER_CONSUMER_SECRET,
			$token['oauth_token'] ?? null,
			$token['oauth_token_secret'] ?? null,
		);
	}

	public function getLoginUrl(): string {
		if (empty(TWITTER_CONSUMER_KEY)) {
			// No twitter app specified. Continuing would throw an exception.
			return URL;
		}
		$auth = self::getTwitterObj();
		$params = ['oauth_callback' => $this->getRedirectUrl()];
		$_SESSION['TwitterToken'] = $auth->oauth('oauth/request_token', $params);
		// 'authenticate' asks for permission only once ('authorize' is every time)
		return $auth->url('oauth/authenticate', $_SESSION['TwitterToken']);
	}

	public function login(): SocialIdentity {
		if ($_SESSION['TwitterToken']['oauth_token'] !== Request::get('oauth_token')) {
			throw new Exception('Unexpected token received from Twitter');
		}
		$helper = self::getTwitterObj($_SESSION['TwitterToken']);
		$accessToken = $helper->oauth(
			'oauth/access_token',
			['oauth_verifier' => Request::get('oauth_verifier')],
		);
		$auth = self::getTwitterObj($accessToken);
		/** @var \stdClass $userInfo */
		$userInfo = $auth->get('account/verify_credentials', ['include_email' => 'true']);
		if ($auth->getLastHttpCode() !== 200) {
			throw new UserError('Connection failed, please try again.');
		}
		return new SocialIdentity($userInfo->id_str, $userInfo->email, $this->getLoginType());
	}

}
