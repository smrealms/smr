<?php
try {
	require_once('config.inc');
	require_once(LIB . 'Default/SmrMySqlDatabase.class.inc');
	require_once(ENGINE . 'Default/smr.inc');
	require_once(ENGINE . 'Default/help.inc');
	?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
            "http://www.w3.org/TR/html4/loose.dtd">

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
					<table>
						<tr>
							<th width="32">
								<img src="<?php echo URL; ?>/images/help/empty.jpg" width="32" height="32">
							</th>
							<th width="32">
								<img src="<?php echo URL; ?>/images/help/empty.jpg" width="32" height="32">
							</th>
							<th width="32">
								<img src="<?php echo URL; ?>/images/help/empty.jpg" width="32" height="32">
							</th>
							<th width="100%" align="center" valign="middle" style="font-size:18pt;font-weight:bold;">Table of Content</th>
							<th width="32"><a href="<?php echo URL; ?>/manual_toc.php"><img src="<?php echo URL; ?>/images/help/contents.jpg" width="32" height="32" border="0"></a></th>
						</tr>
					</table>
				</td>
			</tr>

			<tr>
				<td>
				<?php echo_menu(0); ?>
				</td>
			</tr>

			<tr>
				<td>
					<table>
						<tr>
							<th width="32">
								<img src="<?php echo URL; ?>/images/help/empty.jpg" width="32" height="32">
							</th>
							<th width="32">
								<img src="<?php echo URL; ?>/images/help/empty.jpg" width="32" height="32">
							</th>
							<th width="32">
								<img src="<?php echo URL; ?>/images/help/empty.jpg" width="32" height="32">
							</th>
							<th width="100%" align="center" valign="middle" style="font-size:18pt;font-weight:bold;">Table of Content</th>
							<th width="32"><a href="<?php echo URL; ?>/manual_toc.php"><img src="<?php echo URL; ?>/images/help/contents.jpg" width="32" height="32" border="0"></a></th>
						</tr>
					</table>
				</td>
			</tr>

		</table>

	</body>
</html><?php
}
catch(Exception $e) {
	handleException($e);
}
?>