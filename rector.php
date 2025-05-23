<?php declare(strict_types=1);

use Rector\CodeQuality\Rector\Assign\CombinedAssignRector;
use Rector\Config\RectorConfig;
use Rector\Php53\Rector\FuncCall\DirNameFileConstantToDirConstantRector;
use Rector\Php70\Rector\MethodCall\ThisCallOnStaticMethodToStaticCallRector;
use Rector\Php70\Rector\StmtsAwareInterface\IfIssetToCoalescingRector;
use Rector\Php73\Rector\FuncCall\JsonThrowOnErrorRector;
use Rector\Php74\Rector\Assign\NullCoalescingOperatorRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Php80\Rector\FuncCall\ClassOnObjectRector;
use Rector\Php81\Rector\Array_\FirstClassCallableRector;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Rector\Php83\Rector\ClassConst\AddTypeToConstRector;
use Rector\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector;
use Rector\PHPUnit\Set\PHPUnitSetList;


return RectorConfig::configure()
	->withPaths([
		__DIR__ . '/test',
		__DIR__ . '/src',
	])
	->withImportNames(true, false)
	->withRules([
		AddTypeToConstRector::class,
		AddOverrideAttributeToOverriddenMethodsRector::class,
		ClassOnObjectRector::class,
		ClassPropertyAssignToConstructorPromotionRector::class,
		CombinedAssignRector::class,
		DirNameFileConstantToDirConstantRector::class,
		FirstClassCallableRector::class,
		IfIssetToCoalescingRector::class,
		JsonThrowOnErrorRector::class,
		NullCoalescingOperatorRector::class,
		ReadOnlyPropertyRector::class,
		ThisCallOnStaticMethodToStaticCallRector::class,
	])
	->withSets([
		PHPUnitSetList::PHPUNIT_100,
	])
;
