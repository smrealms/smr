<?php declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php53\Rector\FuncCall\DirNameFileConstantToDirConstantRector;
use Rector\Php70\Rector\StmtsAwareInterface\IfIssetToCoalescingRector;
use Rector\Php73\Rector\FuncCall\JsonThrowOnErrorRector;
use Rector\Php74\Rector\Assign\NullCoalescingOperatorRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
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
		ClassOnObjectRector::class,
		ClassPropertyAssignToConstructorPromotionRector::class,
		DirNameFileConstantToDirConstantRector::class,
		FirstClassCallableRector::class,
		IfIssetToCoalescingRector::class,
		JsonThrowOnErrorRector::class,
		NullCoalescingOperatorRector::class,
	])
	->withSets([
		PHPUnitSetList::PHPUNIT_100,
	])
;
