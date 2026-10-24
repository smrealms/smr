<?php declare(strict_types=1);

namespace Smr\Exceptions;

use Exception;

/**
 * Exception thrown when SectorLock lock acquisition times out.
 */
class SectorLockTimeout extends Exception {
}
