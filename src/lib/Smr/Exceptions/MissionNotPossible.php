<?php declare(strict_types=1);

namespace Smr\Exceptions;

use Exception;

/**
 * Exception thrown when a Mission is not able to be completed
 * (e.g. if a required location does not exist in the game).
 */
class MissionNotPossible extends Exception {
}
