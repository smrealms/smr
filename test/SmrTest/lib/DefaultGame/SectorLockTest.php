<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use Exception;
use Smr\Container\DiContainer;
use Smr\Exceptions\UserError;
use Smr\SectorLock;
use SmrTest\BaseIntegrationSpec;

/**
 * @covers Smr\SectorLock
 */
class SectorLockTest extends BaseIntegrationSpec {

	protected function tablesToTruncate(): array {
		return ['locks_queue'];
	}

	protected function tearDown(): void {
		// Reset the DI container to avoid contaminating other tests
		DiContainer::initialize(false);
	}

	public function test_getInstance_always_returns_same_instance(): void {
		// Given a SectorLock object
		$original = SectorLock::getInstance();
		// When calling getInstance again
		$second = SectorLock::getInstance();
		self::assertSame($original, $second);
	}

	/**
	 * Test that the `resetInstance` function works properly when NPC_SCRIPT is set.
	 */
	public function test_resetInstance_cli(): void {
		// Set the NPC_SCRIPT variable as if this were a CLI program
		DiContainer::getContainer()->set('NPC_SCRIPT', true);

		// Given a SectorLock object
		$original = SectorLock::getInstance();
		// When calling resetInstance
		SectorLock::resetInstance();
		// Expect a new instance when calling getInstance again
		$second = SectorLock::getInstance();
		self::assertNotSame($original, $second);
	}

	/**
	 * resetInstance should throw if NPC_SCRIPT is false (its default value).
	 */
	public function test_resetInstance(): void {
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Only call this function from CLI programs');
		SectorLock::resetInstance();
	}

	public function test_acquire_same_lock_twice(): void {
		$lock = SectorLock::getInstance();
		// Given that we acquire a lock in sector 1
		self::assertTrue($lock->acquire(1, 1, 1));
		// We can call acquire again for sector 1 without throwing
		self::assertFalse($lock->acquire(1, 1, 1));
	}

	public function test_acquire_two_locks_in_the_same_request_throws(): void {
		$lock = SectorLock::getInstance();
		// Given that we acquire a lock in sector 1
		$lock->acquire(1, 1, 1);
		// We throw if we acquire a lock in sector 2 without first releasing
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('This instance has an active lock in a different sector!');
		$lock->acquire(1, 1, 2);
	}

	public function test_acquire_two_locks_in_different_requests_throws(): void {
		// Whereas two different locks in the same request likely indicates
		// a coding error, two different locks in different request likely
		// indicates malicious use (or perhaps spam clicking?).
		$lock1 = SectorLock::getInstance();
		// Given that we acquire a lock
		$lock1->acquire(1, 1, 1);
		// And we use a new SectorLock instance to simulate a separate request
		$lock2 = DiContainer::make(SectorLock::class);
		// Then if that instance tries to acquire a lock (even for the same
		// sector) before the other instance releases, we throw a UserError.
		$this->expectException(UserError::class);
		$this->expectExceptionMessage('Multiple actions cannot be performed at the same time!');
		try {
			$lock2->acquire(1, 1, 1);
		} catch (UserError $e) {
			// Incidentally check that we have set the failed bit correctly
			self::assertTrue($lock2->hasFailed());
			self::assertFalse($lock1->hasFailed());
			throw $e;
		}
	}

	public function test_acquire_after_release(): void {
		$lock = SectorLock::getInstance();
		// Given that we acquire a lock in sector 1
		$lock->acquire(1, 1, 1);
		// Then release
		$lock->release();
		// Expect that we can acquire a lock in sector 2 without throwing
		self::assertTrue($lock->acquire(1, 1, 2));
	}

	public function test_release_same_lock_twice(): void {
		$lock = SectorLock::getInstance();
		// Given that we acquire a lock in sector 1
		$lock->acquire(1, 1, 1);
		// Expect that we can call release
		self::assertTrue($lock->release());
		// Expect that we can call release again without throwing
		self::assertFalse($lock->release());
	}

	public function test_isActive(): void {
		$lock = SectorLock::getInstance();
		// Returns false on a fresh instance
		self::assertFalse($lock->isActive());
		// Returns true after acquire
		$lock->acquire(1, 1, 1);
		self::assertTrue($lock->isActive());
		// Returns false after releasing
		$lock->release();
		self::assertFalse($lock->isActive());
	}

}
