<?php declare(strict_types=1);

namespace SmrTest\PHPStanExtensions;

use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PHPStan\Analyser\ArgumentsNormalizer;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ExtendedMethodReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\Reflection\ParametersAcceptorSelector;

/**
	* Provides PHPStan-normalized call arguments together with their signature.
	*
	* PHPStan's normalizer can reorder named arguments only after selecting the
	* applicable method variant. Extended reflections additionally provide
	* named-argument variants; other reflections use their regular variants.
 */
final readonly class CallArgumentResolver {

	private function __construct(
		private ParametersAcceptor $parametersAcceptor,
		private ?CallLike $reorderedCall,
	) {}

	public static function resolve(
		MethodReflection $methodReflection,
		CallLike $callLike,
		Scope $scope,
	): self {
		$namedArgumentsVariants = $methodReflection instanceof ExtendedMethodReflection
			? $methodReflection->getNamedArgumentsVariants()
			: null;
		$parametersAcceptor = ParametersAcceptorSelector::selectFromArgs(
			$scope,
			$callLike->getArgs(),
			$methodReflection->getVariants(),
			$namedArgumentsVariants,
		);
		$reorderedCall = match (true) {
			$callLike instanceof MethodCall => ArgumentsNormalizer::reorderMethodArguments($parametersAcceptor, $callLike),
			$callLike instanceof New_ => ArgumentsNormalizer::reorderNewArguments($parametersAcceptor, $callLike),
			default => null,
		};
		return new self(
			parametersAcceptor: $parametersAcceptor,
			reorderedCall: $reorderedCall,
		);
	}

	/**
	 * @return ?array<int, \PhpParser\Node\Arg>
	 */
	public function getArguments(): ?array {
		return $this->reorderedCall?->getArgs();
	}

	public function getParametersAcceptor(): ParametersAcceptor {
		return $this->parametersAcceptor;
	}

}
