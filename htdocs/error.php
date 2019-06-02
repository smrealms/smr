<?php require_once('config.inc'); ?>
<!DOCTYPE html>

<html>
	<head>
		<link rel="stylesheet" type="text/css" href="<?php echo DEFAULT_CSS; ?>">
		<link rel="stylesheet" type="text/css" href="<?php echo DEFAULT_CSS_COLOUR; ?>">
		<title>Error</title>
		<meta http-equiv='pragma' content='no-cache'>
	</head>

	<body>

		<table cellspacing='0' cellpadding='0' border='0' width='100%' height='100%'>
			<tr>
				<td></td>
				<td colspan='3' height='1' bgcolor='#0B8D35'></td>
				<td></td>
			</tr>
			<tr>
				<td valign='top' width='135'>&nbsp;</td>
				<td width='1' bgcolor='#0B8D35'></td>
				<td valign='top' width='600' bgcolor='#06240E'>
					<table width='100%' height='100%' border='0' cellspacing='5' cellpadding='5'>
					<tr>
						<td style='vertical-align:top;'>

							<h1>ERROR</h1>

							<p class="big bold"><?php echo (addslashes(htmlentities($_REQUEST['msg'], ENT_NOQUOTES, 'utf-8'))); ?>
							</p>
							<br /><br /><br />
							<p><small>If the error was caused by something you entered, press back and try again.</small></p>
							<p><small>If it was a DB Error, press back and try again, or logoff and log back on.</small></p>
							<p><small>If the error was unrecognizable, please notify the administrators.</small></p>

						</td>
					</tr>
					</table>
				</td>
				<td width='1' bgcolor='#0B8D35'></td>
				<td valign='top' width='135'>&nbsp;</td>
			</tr>
			<tr>
				<td></td>
				<td colspan='3' height='1' bgcolor='#0b8d35'></td>
				<td></td>
			</tr>
		</table>

	</body>
</html>
