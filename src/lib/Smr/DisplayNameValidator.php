<?php declare(strict_types=1);

namespace Smr;

use Smr\Exceptions\UserError;

/**
 * Displayed names (player names, Hall of Fame names, etc.) must all follow a
 * basic set of rules, which are defined here.
 */
class DisplayNameValidator {

	public static function validate(string $name): void {
		if (empty($name)) {
			throw new UserError('You must enter a name!');
		}

		// Prevent any attempts to imitate NPCs
		if (str_contains($name, '[NPC]')) {
			throw new UserError('Names cannot contain "[NPC]".');
		}

		// Only allow printable ascii (no control chars, extended ascii)
		if (!ctype_print($name)) {
			throw new UserError('Names must contain only standard printable characters.');
		}

		// Allow only a limited number of non-alphanumeric characters
		$specialCharCount = 0;
		foreach (str_split($name) as $char) {
			if (!ctype_alnum($char)) {
				$specialCharCount += 1;
			}
		}
		if ($specialCharCount > 4) {
			throw new UserError('You cannot use a name with more than 4 special characters.');
		}
	}

}
