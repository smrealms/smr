<?php declare(strict_types=1);

namespace Smr\StdlibExtensions;

use ArrayIterator;
use InfiniteIterator;

/**
 * Convenience wrapper for cyclically iterating over an array.
 * Is optimized for direct access (rather than with foreach).
 *
 * @template K of array-key
 * @template V
 */
class InfiniteArrayIterator {

	/** @var InfiniteIterator<K, V, ArrayIterator<K, V>> */
	private readonly InfiniteIterator $iter;

	/**
	 * @param array<K, V> $arr Array to iterate over
	 */
	public function __construct(array $arr) {
		$this->iter = new InfiniteIterator(new ArrayIterator($arr));

		// PHP bug prevents IteratorIterator cache from initializing properly.
		// Just rewind to force it to populate its cache.
		$this->iter->rewind();
	}

	/**
	 * Get the current element and then advance the iterator
	 *
	 * @return V
	 */
	public function getAndAdvance(): mixed {
		$current = $this->iter->current();
		$this->iter->next();
		return $current;
	}

	/**
	 * @return V
	 */
	public function current(): mixed {
		return $this->iter->current();
	}

	public function next(): void {
		$this->iter->next();
	}

}
