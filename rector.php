<?php declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php53\Rector\FuncCall\DirNameFileConstantToDirConstantRector;
use Rector\Php73\Rector\FuncCall\JsonThrowOnErrorRector;
use Rector\Php74\Rector\Assign\NullCoalescingOperatorRector;
use Rector\Php80\Rector\FuncCall\ClassOnObjectRector;
use Rector\Php81\Rector\Array_\FirstClassCallableRector;
use Rector\PHPUnit\Set\PHPUnitSetList;

return RectorConfig::configure()
	->withPaths([
		__DIR__ . '/test',
		__DIR__ . '/src',
	])
	->withImportNames(true, false)
	->withRules([
		DirNameFileConstantToDirConstantRector::class,
		JsonThrowOnErrorRector::class,
		NullCoalescingOperatorRector::class,
		ClassOnObjectRector::class,
		FirstClassCallableRector::class,
	])
	->withSets([
		PHPUnitSetList::PHPUNIT_100,
	])
;
