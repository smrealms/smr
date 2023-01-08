<?php declare(strict_types=1);

namespace Smr;

/**
 * Data class for defining the good and transaction type of a TradeGood
 * transaction.
 */
class TradeGoodTransaction {

	public function __construct(
		public readonly int $goodID,
		public readonly TransactionType $transactionType,
	) {}

}
