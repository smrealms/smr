<?php

$fn_invite = function($message) use ($discord) {
	$msg = "$discord->username can be invited to join your server! Just click this link and select your server:\n<https://discordapp.com/oauth2/authorize?&client_id=$discord->id&scope=bot&permissions=0>\n\nNOTE: you must have manager permissions to perform this action.";
	$message->channel->sendMessage($msg);
};

$discord->registerCommand('invite', $fn_invite, ['description' => 'Invite Autopilot to join your server!']);
