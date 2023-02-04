<?php declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php53\Rector\FuncCall\DirNameFileConstantToDirConstantRector;
use Rector\Php81\Rector\Array_\FirstClassCallableRector;

return static function (RectorConfig $rectorConfig): void {
	$rectorConfig->paths([
		__DIR__ . '/test',
		__DIR__ . '/src',
	]);
	$rectorConfig->importNames(true, false);
	$rectorConfig->rule(DirNameFileConstantToDirConstantRector::class);
	$rectorConfig->rule(FirstClassCallableRector::class);
};
