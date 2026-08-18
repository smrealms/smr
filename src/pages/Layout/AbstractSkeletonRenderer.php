<?php declare(strict_types=1);

namespace Smr\Pages\Layout;

abstract class AbstractSkeletonRenderer {

	abstract public static function render(SkeletonData $data): void;

}
