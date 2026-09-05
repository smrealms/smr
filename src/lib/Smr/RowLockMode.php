<?php declare(strict_types=1);

namespace Smr;

/**
 * InnoDB row-locking modes for SELECT statements in an active transaction.
 */
enum RowLockMode: string {

	case Share = 'FOR SHARE';
	case Update = 'FOR UPDATE';

}
