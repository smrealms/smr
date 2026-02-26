<?php declare(strict_types=1);

namespace Smr\Exceptions;

use Exception;

/**
 * Exception thrown when we query the step of a Mission that does
 * not exist (e.g. checking to see if the Mission is complete).
 */
class MissionStepNotFound extends Exception {
}
