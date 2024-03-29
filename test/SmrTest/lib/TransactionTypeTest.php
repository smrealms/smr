<?php declare(strict_types=1);

namespace SmrTest\lib;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Smr\TransactionType;

#[CoversClass(TransactionType::class)]
class TransactionTypeTest extends TestCase {

	public function test_opposite(): void {
		// Make sure Buy and Sell are opposites of each other
		self::assertSame(TransactionType::Buy, TransactionType::Sell->opposite());
		self::assertSame(TransactionType::Sell, TransactionType::Buy->opposite());
	}

}
