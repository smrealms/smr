<?php declare(strict_types=1);

namespace Smr\SocialLogin;

use Exception;
use Smr\Exceptions\SocialLoginInvalidType;

/**
 * Defines the methods to be implemented by each social login platform.
 */
abstract class SocialLogin {

	/**
	 * Provides the canonical name of the platform to use in string comparison.
	 */
	abstract public static function getLoginType(): string;

	/**
	 * Returns a SocialLogin class of the given derived type.
	 */
	public static function get(string $loginType): self {
		return match ($loginType) {
			Facebook::getLoginType() => new Facebook(),
			Twitter::getLoginType() => new Twitter(),
			Google::getLoginType() => new Google(),
			default => throw new SocialLoginInvalidType('Unknown social login type: ' . $loginType),
		};
	}

	public function __construct() {
		// All social logins use a session for authentication
		if (session_status() === PHP_SESSION_NONE) {
			if (!session_start()) {
				throw new Exception('Failed to start social login session');
			}
		}
	}

	/**
	 * Returns the URL that the social platform will redirect to
	 * after authentication.
	 */
	protected function getRedirectUrl(): string {
		return URL . '/login_processing.php?loginType=' . static::getLoginType();
	}

	/**
	 * Returns the URL to authenticate with the social platform.
	 */
	abstract public function getLoginUrl(): string;

	/**
	 * Authenticates with the social platform.
	 */
	abstract public function login(): SocialIdentity;

}
