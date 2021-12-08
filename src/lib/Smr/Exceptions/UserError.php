<?php declare(strict_types=1);

namespace Smr\Exceptions;

/**
 * This exception should be used to pass an error message to the user.
 * It should only encompass errors that can be triggered by otherwise
 * valid user input (i.e. NOT internal errors, request forgery, etc.).
 */
class UserError extends \RuntimeException {}
