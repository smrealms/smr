<?php

// Closes the mysql connection after a command has executed.
// This is necessary to prevent a blocking mysql timeout error.
function mysql_cleanup(callable $func) {

	// Create a new closure that wraps the original closure
	$func_wrapper = function ($message) use ($func) {
		// First, call the original closure
		$func($message);

		// Then, close the mysql connection to prevent timeouts
		$db = new SmrMySqlDatabase();
		$db->close();
	};

	return $func_wrapper;
}

?>
