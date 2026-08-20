<?php declare(strict_types=1);

use Rector\CodeQuality\Rector\Assign\CombinedAssignRector;
use Rector\CodingStyle\Rector\FuncCall\FunctionFirstClassCallableRector;
use Rector\Config\RectorConfig;
use Rector\Php53\Rector\FuncCall\DirNameFileConstantToDirConstantRector;
use Rector\Php70\Rector\MethodCall\ThisCallOnStaticMethodToStaticCallRector;
use Rector\Php70\Rector\StmtsAwareInterface\IfIssetToCoalescingRector;
use Rector\Php73\Rector\FuncCall\ArrayKeyFirstLastRector;
use Rector\Php73\Rector\FuncCall\JsonThrowOnErrorRector;
use Rector\Php74\Rector\Assign\NullCoalescingOperatorRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Php80\Rector\FuncCall\ClassOnObjectRector;
use Rector\Php81\Rector\Array_\ArrayToFirstClassCallableRector;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Rector\Php82\Rector\Class_\ReadOnlyClassRector;
use Rector\Php83\Rector\ClassConst\AddTypeToConstRector;
use Rector\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector;
use Rector\Php84\Rector\Foreach_\ForeachToArrayAllRector;
use Rector\Php84\Rector\Foreach_\ForeachToArrayAnyRector;
use Rector\Php84\Rector\Foreach_\ForeachToArrayFindKeyRector;
use Rector\Php84\Rector\Foreach_\ForeachToArrayFindRector;
use Rector\Php84\Rector\MethodCall\NewMethodCallWithoutParenthesesRector;
use Rector\Php85\Rector\ArrayDimFetch\ArrayFirstLastRector;
use Rector\Php85\Rector\Property\AddOverrideAttributeToOverriddenPropertiesRector;
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
		AddOverrideAttributeToOverriddenPropertiesRector::class,
		ArrayFirstLastRector::class,
		ArrayKeyFirstLastRector::class,
		ArrayToFirstClassCallableRector::class,
		ClassOnObjectRector::class,
		ClassPropertyAssignToConstructorPromotionRector::class,
		CombinedAssignRector::class,
		DirNameFileConstantToDirConstantRector::class,
		ForeachToArrayAllRector::class,
		ForeachToArrayAnyRector::class,
		ForeachToArrayFindKeyRector::class,
		ForeachToArrayFindRector::class,
		FunctionFirstClassCallableRector::class,
		IfIssetToCoalescingRector::class,
		JsonThrowOnErrorRector::class,
		NewMethodCallWithoutParenthesesRector::class,
		NullCoalescingOperatorRector::class,
		ReadOnlyClassRector::class,
		ReadOnlyPropertyRector::class,
		ThisCallOnStaticMethodToStaticCallRector::class,
	])
	->withSets([
		PHPUnitSetList::PHPUNIT_100,
	]);
