<?php declare(strict_types=1);

namespace SmrTest\PHPStanExtensions;

use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\Type;
use Smr\DatabaseResult;

/**
 * Represents a DatabaseResult carrying the inferred Doctrine row type.
 *
 * DatabaseResultDynamicReturnTypeExtension consumes this generic argument to
 * pass row-shape information on to DatabaseRecordObjectType.
 */
class DatabaseResultObjectType extends GenericObjectType {

	public function __construct(Type $rowType) {
		parent::__construct(DatabaseResult::class, [$rowType]);
	}

	public function getRowType(): Type {
		return $this->getTypes()[0];
	}

}
