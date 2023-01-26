<?php declare(strict_types=1);

namespace Smr\SocialLogin;

use Exception;
use Smr\Exceptions\SocialLoginInvalidType;

/**
 * Defines the methods to be implemented by each social login platform.
 */
abstract class SocialLogin {

	private ?string $userID = null;
	private ?string $email = null;
	private bool $valid = false;
	protected ?string $errorMessage = null;

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
	 * After a successful authentication, set credentials.
	 */
	protected function setCredentials(?string $userID, ?string $email): void {
		$this->userID = $userID;
		$this->email = $email;
		$this->valid = !empty($userID);
	}

	/**
	 * Returns the URL that the social platform will redirect to
	 * after authentication.
	 */
	protected function getRedirectUrl(): string {
		return URL . '/login_processing.php?loginType=' . $this->getLoginType();
	}

	/**
	 * Returns the URL to authenticate with the social platform.
	 */
	abstract public function getLoginUrl(): string;

	/**
	 * Authenticates with the social platform.
	 */
	abstract public function login(): self;

	/**
	 * Returns true if the authentication was successful.
	 */
	public function isValid(): bool {
		return $this->valid;
	}

	public function getUserID(): ?string {
		return $this->userID;
	}

	public function getEmail(): ?string {
		return $this->email;
	}

	/**
	 * Returns the authentication error message, if one has been set.
	 */
	public function getErrorMessage(): ?string {
		return $this->errorMessage;
	}

}
