<?php declare(strict_types=1);

namespace SmrTest\lib\SocialLogin;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Smr\Exceptions\UserError;
use Smr\SocialLogin\SocialIdentity;

#[CoversClass(SocialIdentity::class)]
class SocialIdentityTest extends TestCase {

	public function test_happy_path(): void {
		$id = new SocialIdentity('foo', 'bar', 'baz');
		self::assertSame('foo', $id->id);
		self::assertSame('bar', $id->email);
		self::assertSame('baz', $id->type);
	}

	#[TestWith([''])]
	#[TestWith([null])]
	public function test_invalid_id(?string $id): void {
		$this->expectException(UserError::class);
		$this->expectExceptionMessage('Failed to retrieve your bar ID!');
		new SocialIdentity($id, 'foo', 'bar');
	}

	#[TestWith([''])]
	#[TestWith([null])]
	public function test_invalid_email(?string $email): void {
		$this->expectException(UserError::class);
		$this->expectExceptionMessage('An email address is required, but was not found!');
		new SocialIdentity('foo', $email, 'bar');
	}

}
