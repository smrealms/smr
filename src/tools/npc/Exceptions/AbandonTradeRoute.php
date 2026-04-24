<?php declare(strict_types=1);

namespace Smr\Npc\Exceptions;

use Exception;

/**
 * Exception for a when a non-fatal problem is encountered with a trade route,
 * e.g. route is drained or insufficient credits.
 */
class AbandonTradeRoute extends Exception {
}
