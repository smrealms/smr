<?

require_once('../config.inc');
require_once(ENGINE . 'Default/smr.inc');
require_once(LIB . 'Default/SmrMySqlDatabase.class.inc');
require_once(get_file_loc('SmrSession.class.inc'));

require_once(LIB . 'Album/album_functions.php');

// database object
$db = new SmrMySqlDatabase();
$db2 = new SmrMySqlDatabase();
?>
<!doctype html public "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<link rel="stylesheet" type="text/css" href="<?php echo URL;?>/css/classic.css">
<title>Space Merchant Realms - Photo Album</title>
<meta http-equiv="pragma" content="no-cache">
</head>
<body>

<table width="850" border="0" align="center" cellpadding="0" cellspacing="0" >
<tr>
<td align="center" colspan="2"><h1>Space Merchant Realms - Photo Album</h1></td>
</tr>
<tr>
<td>
<table width="750" border="0" cellspacing="0" cellpadding="0">
<tr>
<td>

<table cellspacing="0" cellpadding="0" border="0" width="700">
<tr>
<td colspan="3" height="1" bgcolor="#0B8D35"></td>
</tr>
<tr>
<td width="1" bgcolor="#0B8D35"></td>
<td align="left" valign="top" bgcolor="#06240E">
<table width="100%" height="100%" border="0" cellspacing="5" cellpadding="5">
<tr>
<td valign="top">
<?php
if (!empty($_SERVER['QUERY_STRING']))
{
	// query string should be a nick or some letters of a nick
	$query = mysql_escape_string(urldecode($_SERVER['QUERY_STRING']));

	$db->query('SELECT album.account_id as album_id
				FROM album NATURAL JOIN account
				WHERE hof_name LIKE '.$db->escapeString($query.'%').' AND
					  approved = \'YES\'
				ORDER BY hof_name');

	if ($db->getNumRows() > 1)
	{
		$db2->query('SELECT album.account_id as album_id
				FROM album NATURAL JOIN account
				WHERE hof_name = '.$db->escapeString($query).' AND
					  approved = \'YES\'
				ORDER BY hof_name');
		
		if ($db2->nextRecord())
			album_entry($db2->getField('album_id'));
		else
		{
			// get all id's and build array
			$album_ids = array();
	
			while ($db->nextRecord())
				$album_ids[] = $db->getField('album_id');
	
			// double check if we have id's
			if (count($album_ids) > 0)
				search_result($album_ids);
			else
				main_page();
		}

	}
	elseif ($db->getNumRows() == 1)
	{
		if ($db->nextRecord())
			album_entry($db->getField('album_id'));
		else
			main_page();
	}
	else
		main_page();
}
else
	main_page();
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
<td align="left" valign="top" bgcolor="#06240E">
<table width="100%" height="100%" border="0" cellspacing="5" cellpadding="5">
<tr>
<td valign="top" align="center">
<form action="<?php echo URL; ?>/album/search_processing.php">
Quick Search:<br />
<input type="text" name="nick" size="10" id="InputFields"><br />
<input type="submit" value="Search" id="InputFields">
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
	<td align='right' style='font-size:65%;'>
	    &copy; 2002-2007 by <a href="<?php echo URL; ?>"><?php echo URL; ?></a><br />
	    Hosted by <a href='http://www.fem.tu-ilmenau.de/index.php?id=93&L=1' target='fem'>FeM</a>
	</td>

</tr>
</table>

</body>
</html>

