<?php declare(strict_types=1);

// Closes the mysql connection after a command has executed.
// This is necessary to prevent a blocking mysql timeout error.
function mysql_cleanup(callable $func) : callable {

	// Create a new closure that wraps the original closure
	$func_wrapper = function($message, $params) use ($func) {
		// First, call the original closure
		try {
			$func($message, $params);
		} catch (Throwable $e) {
			logException($e);
			$message->reply('I encountered an error. Please report this to an admin!')
				->done(null, 'logException');
		}

		// Then, close the mysql connection to prevent timeouts
		$db = Smr\Database::getInstance();
		$db->close();
	};

	return $func_wrapper;
}
