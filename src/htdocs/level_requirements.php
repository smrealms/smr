<?php declare(strict_types=1);

use Smr\PlayerLevel;

try {
	require_once('../bootstrap.php'); ?>

<!DOCTYPE html>
<html>
	<head>
		<link rel="stylesheet" type="text/css" href="<?php echo DEFAULT_CSS; ?>">
		<link rel="stylesheet" type="text/css" href="<?php echo DEFAULT_CSS_COLOUR; ?>">
		<title>Level Requirements</title>
		<meta http-equiv="pragma" content="no-cache">
	</head>

	<body>
	<table class="standard center">

	<tr>
		<th>Rank Level</th>
		<th>Rank Name</th>
		<th>Required Experience</th>
	</tr><?php
	foreach (PlayerLevel::getAll() as $levelID => $level) { ?>
		<tr>
			<td><?php echo $levelID; ?></td>
			<td><?php echo $level->name; ?></td>
			<td><?php echo $level->expRequired; ?></td>
		</tr><?php
	} ?>
	</table><?php
} catch (Throwable $e) {
	handleException($e);
}
