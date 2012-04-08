<?php
try {
	require_once('config.inc');
	require_once(LIB . 'Default/SmrMySqlDatabase.class.inc');

	$db = new SmrMySqlDatabase(); ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
            "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<link rel="stylesheet" type="text/css" href="<?php echo DEFAULT_CSS; ?>">
		<link rel="stylesheet" type="text/css" href="<?php echo DEFAULT_CSS_COLOUR; ?>">
		<title>Level Requirements</title>
		<meta http-equiv="pragma" content="no-cache">
	</head>

	<body>
	<table class="standard">

	<tr>
		<th align="center" style="color:#80C870;">Rank Level</th>
		<th align="center" style="color:#80C870;">Rank Name</th>
		<th align="center" style="color:#80C870;">Required Experience</th>
	</tr><?php
	$db->query('SELECT * FROM level ORDER BY level_id');
	while ($db->nextRecord()) {
		$level = $db->getField('level_id');
		$name = $db->getField('level_name');
		$require = $db->getField('requirement'); ?>
		<tr>
		<td align="center"><?php echo $level; ?></td>
		<td align="center"><?php echo $name; ?></td>
		<td align="center"><?php echo $require; ?></td>
		</tr><?php
	} ?>
	</table><?php
}
catch(Exception $e) {
	handleException($e);
}
?>