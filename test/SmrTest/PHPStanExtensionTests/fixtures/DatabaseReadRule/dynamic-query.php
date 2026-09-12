<?php declare(strict_types=1);

namespace SmrTest\PHPStanExtensionTests\Fixtures\DatabaseReadRule;

use Smr\Database;

function dynamicQuery(Database $db, string $query): void {
	$db->read($query);
}
