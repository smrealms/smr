<?php declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php81\Rector\Array_\FirstClassCallableRector;

return static function (RectorConfig $rectorConfig): void {
	$rectorConfig->paths([
		__DIR__ . '/test',
		__DIR__ . '/src',
	]);
	$rectorConfig->rule(FirstClassCallableRector::class);
};
