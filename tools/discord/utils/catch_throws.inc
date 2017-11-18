<?php

// This function is needed because DiscordPHP has a swallowed
// Promise rejection somewhere, which causes error messages from
// registered commands to be suppressed.
//
// To regain visibility of the error messages, we simply wrap
// the command functions in a try-catch and print any caught
// errors.

function catch_throws($func) {
    try {
        $func();
    } catch (Throwable $err) {
        echo (string)$err;
    }
}

?>
