<?php declare(strict_types=1);

namespace Smr\Container;

/**
 * Extends a Di\Container to allow for introspection and resetting.
 */
trait ResettableContainerTrait {

	/**
	 * Test if the entry given by $name has been initialized in the container.
	 *
	 * @param string $name Entry name or a class name.
	 * @return bool Whether or not the entry is initialized.
	 */
	public function initialized(string $name): bool {
		return array_key_exists($name, $this->resolvedEntries);
	}

	/**
	 * Unset the entry given by $name in the container.
	 *
	 * A subsequent call to get() will create a new instance of this entry,
	 * according to its definition (if it has one). This can be useful to
	 * release resources that are no longer in use or that need to be reset.
	 *
	 * @param string $name Entry name or a class name.
	 */
	public function reset(string $name): void {
		unset($this->resolvedEntries[$name]);
	}

}
