<?php declare(strict_types=1);

namespace Smr\SocialLogin;

use Abraham\TwitterOAuth\TwitterOAuth;
use Exception;
use Smr\ArrayValue;
use Smr\Exceptions\UserError;
use Smr\Request;
use Smr\TypeHelpers\Type;

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
		if (TWITTER_CONSUMER_KEY === '') {
			// No twitter app specified. Continuing would throw an exception.
			return URL;
		}
		$auth = self::getTwitterObj();
		$params = ['oauth_callback' => $this->getRedirectUrl()];
		$_SESSION['TwitterToken'] = $auth->oauth('oauth/request_token', $params);
		// 'authenticate' asks for permission only once ('authorize' is every time)
		return $auth->url('oauth/authenticate', $this->getSessionToken());
	}

	public function login(): SocialIdentity {
		$token = $this->getSessionToken();
		if ($token['oauth_token'] !== Request::get('oauth_token')) {
			throw new Exception('Unexpected token received from Twitter');
		}
		$helper = self::getTwitterObj($token);
		$accessToken = $helper->oauth(
			'oauth/access_token',
			['oauth_verifier' => Request::get('oauth_verifier')],
		);
		$auth = self::getTwitterObj(self::validateToken($accessToken));
		/** @var object{id_str: ?string, email: ?string}&\stdClass $userInfo */
		$userInfo = $auth->get('account/verify_credentials', ['include_email' => 'true']);
		if ($auth->getLastHttpCode() !== 200) {
			throw new UserError('Connection failed, please try again.');
		}
		return new SocialIdentity($userInfo->id_str, $userInfo->email, static::getLoginType());
	}

	/**
	 * @return array<string, string>
	 */
	private function getSessionToken(): array {
		$innerType = Type::array(Type::string(), Type::string());
		$token = ArrayValue::get($_SESSION, 'TwitterToken', $innerType);
		if (!isset($token['oauth_token'], $token['oauth_token_secret'])) {
			throw new Exception('Twitter login session has expired.');
		}
		return $token;
	}

	/**
	 * @param array<mixed> $token
	 * @return array<string, string>
	 */
	private static function validateToken(array $token): array {
		$type = Type::array(valueType: Type::string(), keyType: Type::string());
		if (!$type->accepts($token)) {
			throw new Exception('Twitter access token has unexpected type.');
		}
		return $token;
	}

}
