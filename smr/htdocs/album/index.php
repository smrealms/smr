<?

include('../config.inc');
require_once($ENGINE . 'Old_School/smr.inc');
include($LIB . 'global/smr_db.inc');
require_once(get_file_loc('SmrSession.class.inc'));

include('album_functions.php');

// get session
$session = new SmrSession();

// database object
$db = new SMR_DB();
$db2 = new SMR_DB();
echo('<!doctype html public "-//W3C//DTD HTML 4.0 Transitional//EN">');
echo('<html>');
echo('<head>');
echo('<link rel="stylesheet" type="text/css" href="'.$URL.'/default.css">');
echo('<title>Space Merchant Realms - Photo Album</title>');
echo('<meta http-equiv="pragma" content="no-cache">');
echo('</head>');
echo('<body>');

echo('<table width="850" border="0" align="center" cellpadding="0" cellspacing="0" >');
echo('<tr>');
echo('<td align="center" colspan="2"><h1>Space Merchant Realms - Photo Album</h1></td>');
echo('</tr>');
echo('<tr>');
echo('<td>');
echo('<table width="750" border="0" cellspacing="0" cellpadding="0">');
echo('<tr>');
echo('<td>');

echo('<table cellspacing="0" cellpadding="0" border="0" width="700">');
echo('<tr>');
echo('<td colspan="3" height="1" bgcolor="#0B8D35"></td>');
echo('</tr>');
echo('<tr>');
echo('<td width="1" bgcolor="#0B8D35"></td>');
echo('<td align="left" valign="top" bgcolor="#06240E">');
echo('<table width="100%" height="100%" border="0" cellspacing="5" cellpadding="5">');
echo('<tr>');
echo('<td valign="top">');

if (!empty($_SERVER['QUERY_STRING'])) {

	// query string should be a nick or some letters of a nick
	$query = mysql_escape_string(urldecode($_SERVER['QUERY_STRING']));

	$db->query('SELECT album.account_id as album_id
				FROM album NATURAL JOIN account_has_stats
				WHERE HoF_name LIKE '.$db->escapeString($query.'%').' AND
					  approved = \'YES\'
				ORDER BY HoF_name');

	if ($db->nf() > 1) {

		$db2->query('SELECT album.account_id as album_id
				FROM album NATURAL JOIN account_has_stats
				WHERE HoF_name = '.$db->escapeString($query).' AND
					  approved = \'YES\'
				ORDER BY HoF_name');
		
		if ($db2->next_record())
			album_entry($session, $db2->f('album_id'));
		else {
			
			// get all id's and build array
			$album_ids = array();
	
			while ($db->next_record())
				$album_ids[] = $db->f('album_id');
	
			// double check if we have id's
			if (count($album_ids) > 0)
				search_result($album_ids);
			else
				main_page();
		}

	} elseif ($db->nf() == 1) {

		if ($db->next_record())
			album_entry($session, $db->f('album_id'));
		else
			main_page();

	} else
		main_page();

} else
	main_page();

echo('</td>');
echo('</tr>');
echo('</table>');
echo('</td>');
echo('<td width="1" bgcolor="#0B8D35"></td>');
echo('</tr>');
echo('<tr>');
echo('<td colspan="3" height="1" bgcolor="#0b8d35"></td>');
echo('</tr>');
echo('</table>');

echo('</td>');
echo('<td width="20">&nbsp;</td>');
echo('<td height="100%">');

echo('<table cellspacing="0" cellpadding="0" border="0" width="150" height="100%">');
echo('<tr>');
echo('<td colspan="3" height="1" bgcolor="#0B8D35"></td>');
echo('</tr>');
echo('<tr>');
echo('<td width="1" bgcolor="#0B8D35"></td>');
echo('<td align="left" valign="top" bgcolor="#06240E">');
echo('<table width="100%" height="100%" border="0" cellspacing="5" cellpadding="5">');
echo('<tr>');
echo('<td valign="top" align="center">');
echo('<form action="'.$URL.'/album/search_processing.php">');
echo('Quick Search:<br>');
echo('<input type="text" name="nick" size="10" id="InputFields"><br>');
echo('<input type="submit" value="Search" id="InputFields">');
echo('</form>');

echo('</td>');
echo('</tr>');
echo('</table>');
echo('</td>');
echo('<td width="1" bgcolor="#0B8D35"></td>');
echo('</tr>');
echo('<tr>');
echo('<td colspan="3" height="1" bgcolor="#0b8d35"></td>');
echo('</tr>');
echo('</table>');

echo('</td>');
echo('</tr>');
echo('</table>');
echo('</td>');
echo('</tr>');

echo('<tr>');
?>
<td align='right' style='font-size:65%;'>
    &copy; 2002-2007 by <a href='<?php echo($URL); ?>'><?php echo($URL); ?></a><br/>
    Hosted by <a href='http://www.fem.tu-ilmenau.de/index.php?id=93&L=1' target='fem'>FeM</a>
</td>

</tr>
</table>

</body>
</html>

