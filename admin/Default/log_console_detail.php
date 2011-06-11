<?php

function build_list($array)
{
	$list = '';
	if (!is_array($array))
		return false;

	foreach ($array as $element)
	{
		if (!empty($list))
			$list .= ', ';
		$list .= $element;
	}
	
	return '(' . $list . ')';
}

$template->assign('PageTopic','Log Console - Detail');

// get the account_ids from last form
if (isset($_REQUEST['account_ids']))
	SmrSession::updateVar('account_ids',$_REQUEST['account_ids']);
$account_ids = $var['account_ids'];

// get the account_ids from last form
if (isset($_REQUEST['log_type_ids']))
	SmrSession::updateVar('log_type_ids',$_REQUEST['log_type_ids']);
$log_type_ids = $var['log_type_ids'];

// build a list of it like: (id1, id2, id3)
$account_list = build_list($account_ids);

// initialize order items
if (!isset($var['item']))
	$var['item'] = 'time';
if (!isset($var['order']))
	$var['order'] = 'ASC';

// nothing marked?
if (!$account_list)
	$PHP_OUTPUT.=create_error('You have to select the log files you want to view/delete!');

$action = $_REQUEST['action'];
if ($action == 'Delete')
{
	$account_list = build_list($account_ids);

	// get rid of all entries
	$db->query('DELETE FROM account_has_logs WHERE account_id IN '.$account_list);
	$db->query('DELETE FROM log_has_notes WHERE account_id IN '.$account_list);

	$PHP_OUTPUT.=('Operation was completed successfully!');
}
else
{
	// a second db object
	$db2 = new SmrMySqlDatabase();

	// predefine a color array
	$avail_colors = array('#FFFFFF', '#FF0000', '#00FF00', '#0000FF');

	// now assign each account id a color
	for ($i = 0; $i < count($account_ids); $i++)
	{
		// get current id
		$curr_account_id = $account_ids[$i];

		// assign it a color
		$colors[$curr_account_id] = $avail_colors[$i % count($avail_colors)];
	}

	$PHP_OUTPUT.=('<table>');
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<td valign="top" width="50%">');

	$PHP_OUTPUT.=('<p>Show the following types:</p>');

	// *********************************
	// *
	// * L o g   T y p e s
	// *
	// *********************************
	$container = create_container('skeleton.php', 'log_console_detail.php');
	$container['account_ids'] = $account_ids;
	transfer('order');
	transfer('item');
	$PHP_OUTPUT.=create_echo_form($container);
	$PHP_OUTPUT.=create_submit('Update');
	$PHP_OUTPUT.=('<br /><br />');

	$db->query('SELECT * FROM log_type');
	while ($db->nextRecord())
	{
		$PHP_OUTPUT.=('<input type="checkbox" name="log_type_ids[' . $db->getField('log_type_id') . ']"');
		if ($log_type_ids[$db->getField('log_type_id')])
		{
			$PHP_OUTPUT.=(' checked');
			if (!empty($log_type_id_list))
				$log_type_id_list .= ',';
			$log_type_id_list .= $db->getField('log_type_id');

		}
		$PHP_OUTPUT.=('>' . $db->getField('log_type_entry') . '<br />');
	}
	$PHP_OUTPUT.=('</form>');

	$PHP_OUTPUT.=('</td>');
	$PHP_OUTPUT.=('<td valign="top" width="50%">');

	// *********************************
	// *
	// * N o t e s
	// *
	// *********************************
	if (count($account_ids) == 1)
		$PHP_OUTPUT.=('<p>Change the notes for this user:</p>');
	else
		$PHP_OUTPUT.=('<p>Change the notes for these users:</p>');

	$container = create_container('log_notes_processing.php', '');
	$container['account_ids'] = $account_ids;
	$container['log_type_ids'] = $log_type_ids;
	transfer('order');
	transfer('item');
	$PHP_OUTPUT.=create_echo_form($container);

	$PHP_OUTPUT.=('<input type="hidden" name="account_ids" value="'.$account_ids.'">');
	$PHP_OUTPUT.=create_submit('Save');
	$PHP_OUTPUT.=('<br /><br />');

	$log_notes = array();

	// get notes from db
	$db->query('SELECT * FROM log_has_notes WHERE account_id IN '.$account_list);
	while ($db->nextRecord())
		$log_notes[] = $db->getField('notes');

	// get rid of double values
	$log_notes = array_unique($log_notes);

	// flattens array
	foreach ($log_notes as $note)
	{
		if ($flat_notes)
			$flat_notes .= EOL;
		$flat_notes .= $note;
	}

	$PHP_OUTPUT.=('<textarea name="notes" style="width:300px; height:200px;" id="InputFields">'.$flat_notes.'</textarea>');
	$PHP_OUTPUT.=('</form>');

	$PHP_OUTPUT.=('</td>');
	$PHP_OUTPUT.=('</tr>');
	$PHP_OUTPUT.=('</table>');

	// *********************************
	// *
	// * C o l o r   L e g e n d
	// *
	// *********************************
	$PHP_OUTPUT.=('Following colors will be used:');
	$PHP_OUTPUT.=('<ul>');
	foreach ($colors as $id => $color)
	{
		$db->query('SELECT login FROM account WHERE account_id = ' . $id);
		if ($db->nextRecord())
			$PHP_OUTPUT.=('<li style="color:'.$color.';">' . $db->getField('login') . '</li>');
	}
	$PHP_OUTPUT.=('</ul>');

	// *********************************
	// *
	// * L o g   T a b l e
	// *
	// *********************************
	$PHP_OUTPUT.=('<table class="standard" width="100%">');
	$PHP_OUTPUT.=('<tr>');

	$container = create_container('skeleton.php', 'log_console_detail.php');
	$container['account_ids'] = $account_ids;
	$container['log_type_ids'] = $log_type_ids;
	if ($var['order'] == 'ASC')
		$container['order'] = 'DESC';
	else
		$container['order'] = 'ASC';

	$container['item'] = 'time';
	$PHP_OUTPUT.=create_link($container, '<th style="cursor:hand;">Time</th>');
	$PHP_OUTPUT.=('<th>Log Type</th>');
	$container['item'] = 'sector_id';
	$PHP_OUTPUT.=create_link($container, '<th style="cursor:hand;">Sector</th>');
	$PHP_OUTPUT.=('<th>Message</th>');
	$PHP_OUTPUT.=('</tr>');

	if (empty($log_type_id_list))
		$log_type_id_list = 0;

	$db->query('SELECT * FROM account_has_logs WHERE account_id IN '.$account_list.' AND log_type_id IN ('.$log_type_id_list.') ORDER BY ' . $var['item'] . ' ' . $var['order']);
	while ($db->nextRecord())
	{
		$account_id		= $db->getField('account_id');
		$microtime		= $db->getMicrotime('microtime');
		$message		= stripslashes($db->getField('message'));
		$log_type_id	= $db->getField('log_type_id');
		$sector_id		= $db->getField('sector_id');

		// generate style string
		$style = ' style="color:' . $colors[$account_id] . ';"';

		$PHP_OUTPUT.=('<tr>');
		$PHP_OUTPUT.=('<td'.$style.'>' . date(DATE_FULL_SHORT, explodeElement(' ',$microtime,0)) . ' ' . explodeElement(' ',$microtime,1) . 'us</td>');

		$db2->query('SELECT * FROM log_type WHERE log_type_id = '.$log_type_id);
		if ($db2->nextRecord())
			$PHP_OUTPUT.=('<td align="center"'.$style.'>' . $db2->getField('log_type_entry') . '</td>');
		else
			$PHP_OUTPUT.=('<td align="center"'.$style.'>unknown</td>');

		$PHP_OUTPUT.=('<td align="center"'.$style.'>'.$sector_id.'</td>');
		$PHP_OUTPUT.=('<td'.$style.'>'.$message.'</td>');
		$PHP_OUTPUT.=('</tr>');
	}
	$PHP_OUTPUT.=('</table>');
}

$PHP_OUTPUT.=('<p>');
$container = create_container('skeleton.php', 'log_console.php');
$container['account_ids'] = $account_ids;
$PHP_OUTPUT.=create_link($container, '<b>&lt; Back</b>');
$PHP_OUTPUT.=('</p>');

?>