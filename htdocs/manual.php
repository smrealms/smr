<?php
try {
	require_once('config.inc');
	require_once(LIB . 'Default/smr.inc');
	require_once(ENGINE . 'Default/help.inc');

	$topic_id = $_SERVER['QUERY_STRING'];
	if (empty($topic_id) || !is_numeric($topic_id))
		$topic_id = 1;
	?>
<!DOCTYPE html>

<html>
	<head>
		<link rel="stylesheet" type="text/css" href="<?php echo DEFAULT_CSS; ?>">
		<link rel="stylesheet" type="text/css" href="<?php echo DEFAULT_CSS_COLOUR; ?>">
		<title>Space Merchant Realms - Manual</title>
		<meta http-equiv="pragma" content="no-cache">
	</head>

	<body>

		<table width="100%" border="0">
			<tr>
				<td>
					<?php echo_nav($topic_id); ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo_content($topic_id); ?>
				</td>
			</tr>

			<tr>
				<td>
					<?php echo_subsection($topic_id); ?>
				</td>
			</tr>

			<tr>
				<td>
					<?php echo_nav($topic_id); ?>
				</td>
			</tr>

		</table>

	</body>
	</html><?php
}
catch (Throwable $e) {
	handleException($e);
}
?>
