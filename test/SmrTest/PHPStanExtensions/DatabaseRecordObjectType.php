<?php declare(strict_types=1);

namespace SmrTest\PHPStanExtensions;

use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\Type;
use Smr\DatabaseRecord;

/**
 * Represents a DatabaseRecord carrying the constant-array type of its row.
 *
 * The generic argument is internal type metadata used by the record getter
 * extension and rule; runtime DatabaseRecord objects remain unchanged.
 */
class DatabaseRecordObjectType extends GenericObjectType {

	public function __construct(Type $rowType) {
		parent::__construct(DatabaseRecord::class, [$rowType]);
	}

	public function getRowType(): Type {
		return $this->getTypes()[0];
	}

}
