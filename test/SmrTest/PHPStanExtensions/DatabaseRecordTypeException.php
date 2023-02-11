<?php declare(strict_types=1);

namespace SmrTest\PHPStanExtensions;

use Exception;

/**
 * Signals a field absent from an inferred row or incompatible with a getter.
 *
 * The shared resolver throws this instead of deciding how PHPStan surfaces
 * the issue: its RTE caller falls back to the declared getter type and its
 * Rule caller emits an identifier-bearing diagnostic.
 */
class DatabaseRecordTypeException extends Exception {
}
