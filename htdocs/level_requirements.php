<?php
try {
	require_once('config.inc');
?>

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
	$levels = Globals::getLevelRequirements();
	foreach ($levels as $levelID => $level) { ?>
		<tr>
			<td><?php echo $levelID; ?></td>
			<td><?php echo $level['Name']; ?></td>
			<td><?php echo $level['Requirement']; ?></td>
		</tr><?php
	} ?>
	</table><?php
} catch (Throwable $e) {
	handleException($e);
}
?>
