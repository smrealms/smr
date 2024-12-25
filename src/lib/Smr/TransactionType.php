<?php declare(strict_types=1);

namespace Smr;

/**
 * Enumerates the types of trade transactions that players may have with ports.
 * Note that all transactions are from the perspective of the player!
 */
enum TransactionType: string {

	// Backing values cannot be changed, since they map to database values.
	case Buy = 'Buy';
	case Sell = 'Sell';

	public function opposite(): self {
		return match ($this) {
			self::Buy => self::Sell,
			self::Sell => self::Buy,
		};
	}

	// This is *NOT* one of the enum cases, but it logistically makes sense to
	// include it in the same namespace as the first-class transactions.
	public const string STEAL = 'Steal';

}
