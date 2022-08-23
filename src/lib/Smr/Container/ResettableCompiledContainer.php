<?php declare(strict_types=1);

namespace Smr\Container;

use DI\CompiledContainer;

class ResettableCompiledContainer extends CompiledContainer {

	use ResettableContainerTrait;

}
