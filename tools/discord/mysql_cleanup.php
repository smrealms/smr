<?php

// Closes the mysql connection after a command has executed.
// This is necessary to prevent a blocking mysql timeout error.
function mysql_cleanup(callable $func) {

	// Create a new closure that wraps the original closure
	$func_wrapper = function($message, $params) use ($func) {
		// First, call the original closure
		try {
			$func($message, $params);
		} catch (Throwable $e) {
			print('Error in ' . $e->getFile() . ' line ' . $e->getLine() . ':' . EOL);
			print($e->getMessage() . EOL);
			$message->reply('I encountered an error. Please report this to an admin!');
		}

		// Then, close the mysql connection to prevent timeouts
		$db = new SmrMySqlDatabase();
		$db->close();
	};

	return $func_wrapper;
}
