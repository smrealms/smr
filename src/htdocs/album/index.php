<?php declare(strict_types=1);

use Smr\Database;
use Smr\Epoch;
use Smr\Request;

try {
	require_once('../../bootstrap.php');
	require_once(LIB . 'Album/album_functions.php');

	// database object
	$db = Database::getInstance();
	?>
	<!DOCTYPE html>
	<html>
	<head>
		<link rel="stylesheet" type="text/css" href="/<?php echo DEFAULT_CSS; ?>">
		<link rel="stylesheet" type="text/css" href="/<?php echo DEFAULT_CSS_COLOUR; ?>">
		<title>Space Merchant Realms - Photo Album</title>
		<meta http-equiv="pragma" content="no-cache">
	</head>
	<body>

	<table class="center" width="850" border="0" cellpadding="0" cellspacing="0" >
	<tr>
	<td colspan="2"></td>
	</tr>
	<tr>
	<td>
	<table width="750" border="0" cellspacing="0" cellpadding="0">
	<tr>
	<td>

	<table cellspacing="0" cellpadding="0" border="0" width="700">
	<tr><td class="center" colspan="3"><h1>Space Merchant Realms - Photo Album</h1></td></tr>
	<tr>
	<td colspan="3" height="1" bgcolor="#0B8D35"></td>
	</tr>
	<tr>
	<td width="1" bgcolor="#0B8D35"></td>
	<td class="left" valign="top" bgcolor="#06240E">
	<table width="100%" height="100%" border="0" cellspacing="5" cellpadding="5">
	<tr>
	<td valign="top">
	<?php
	if (Request::has('nick')) {
		$query = urldecode(Request::get('nick'));

		$dbResult = $db->read('SELECT account_id as album_id
					FROM album JOIN account USING(account_id)
					WHERE hof_name LIKE ' . $db->escapeString($query . '%') . ' AND
						  approved = \'YES\'
					ORDER BY hof_name');

		if ($dbResult->getNumRecords() > 1) {
			$dbResult2 = $db->read('SELECT account_id as album_id
					FROM album JOIN account USING(account_id)
					WHERE hof_name = ' . $db->escapeString($query) . ' AND
						  approved = \'YES\'
					ORDER BY hof_name');

			if ($dbResult2->hasRecord()) {
				album_entry($dbResult2->record()->getInt('album_id'));
			} else {
				// get all id's and build array
				$album_ids = [];

				foreach ($dbResult->records() as $dbRecord) {
					$album_ids[] = $dbRecord->getInt('album_id');
				}

				// double check if we have id's
				if (count($album_ids) > 0) {
					search_result($album_ids);
				} else {
					main_page();
				}
			}

		} elseif ($dbResult->getNumRecords() == 1) {
			album_entry($dbResult->record()->getInt('album_id'));
		} else {
			main_page();
		}
	} else {
		main_page();
	}
} catch (Throwable $e) {
	handleException($e);
}
?>
</td>
</tr>
</table>
</td>
<td width="1" bgcolor="#0B8D35"></td>
</tr>
<tr>
<td colspan="3" height="1" bgcolor="#0b8d35"></td>
</tr>
</table>

</td>
<td width="20">&nbsp;</td>
<td height="100%">

<table cellspacing="0" cellpadding="0" border="0" width="150" height="100%">
<tr>
<td colspan="3" height="1" bgcolor="#0B8D35"></td>
</tr>
<tr>
<td width="1" bgcolor="#0B8D35"></td>
<td valign="top" bgcolor="#06240E">
<table width="100%" height="100%" border="0" cellspacing="5" cellpadding="5">
<tr>
<td valign="top" class="center">
<form>
Quick Search:<br />
<input type="text" name="nick" size="10"><br />
<input type="submit" value="Search">
</form>

</td>
</tr>
</table>
</td>
<td width="1" bgcolor="#0B8D35"></td>
</tr>
<tr>
<td colspan="3" height="1" bgcolor="#0b8d35"></td>
</tr>
</table>

</td>
</tr>
</table>
</td>
</tr>

<tr>
	<td class="left" style='font-size:65%;'>
		&copy; 2002-<?php echo date('Y', Epoch::time()); ?> by <a href="<?php echo URL; ?>"><?php echo URL; ?></a><br />
		Hosted by <a href='http://www.fem.tu-ilmenau.de/' target='fem'>FeM</a>
	</td>
</tr>
</table>

</body>
</html>
