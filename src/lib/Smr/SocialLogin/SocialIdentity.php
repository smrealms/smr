<?php declare(strict_types=1);

namespace Smr\SocialLogin;

use Smr\Exceptions\UserError;

class SocialIdentity {

	public readonly string $type;
	/** @var non-empty-string */
	public readonly string $id;
	/** @var non-empty-string */
	public readonly string $email;

	public function __construct(?string $id, ?string $email, string $type) {
		if ($id === null || $id === '') {
			throw new UserError('Failed to retrieve your ' . $type . ' ID!');
		}
		if ($email === null || $email === '') {
			throw new UserError('An email address is required, but was not found!');
		}
		$this->id = $id;
		$this->email = $email;
		$this->type = $type;
	}

}
