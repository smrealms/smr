<?php

$smarty->assign('PageTopic','Change Log');

$db2 = new SMR_DB();
$first_entry = true;
$link_set_live = true;

$db->query('SELECT *
			FROM version
			ORDER BY version_id DESC');
while ($db->next_record()) {

	$version_id = $db->f('version_id');
	$version = $db->f('major_version') . '.' . $db->f('minor_version') . '.' . $db->f('patch_level');
	$went_live = $db->f('went_live');

	if ($went_live > 0) {

		// from this point on we don't create links to set a version to live
		$link_set_live = false;

		// get human readable format for date
		$went_live = date('m/d/Y - h:i A', $went_live);

	} else {

		if ($link_set_live) {


			$container = array('url' => 'changelog_set_live_processing.php',
							   'version_id' => $version_id
							  );
			$went_live = create_link($container, 'never');


		} else
			$went_live = 'never';

	}

	$PHP_OUTPUT.=('<b><small>'.$version.' ('.$went_live.'):</small></b>');

	$PHP_OUTPUT.=('<ul>');

	$PHP_OUTPUT.=('<table border="0"">');

	$db2->query('SELECT *
				FROM changelog
				WHERE version_id = '.$version_id.'
				ORDER BY changelog_id');
	while ($db2->next_record()) {

		$PHP_OUTPUT.=('<tr>');
		$PHP_OUTPUT.=('<td valign="top"><li></td>');
		$PHP_OUTPUT.=('<td>' . stripslashes($db2->f('change_title')) . '<br /><small>' . stripslashes($db2->f('change_message')) . '</small></td>');
		$PHP_OUTPUT.=('<td>&nbsp;</td>');
		$PHP_OUTPUT.=('</tr>');

	}

	if ($first_entry) {

		$first_entry = false;

		$container = array('url' => 'changelog_add_processing.php',
						   'version_id' => $version_id
						  );
		$PHP_OUTPUT.=create_echo_form($container);
		$PHP_OUTPUT.=('<tr><td colspan=3">&nbsp;</td></tr>');

		$PHP_OUTPUT.=('<tr>');
		$PHP_OUTPUT.=('<td rowspan="5">&nbsp;</td>');
		$PHP_OUTPUT.=('<td colspan="2"><small>Title:</small></td>');
		$PHP_OUTPUT.=('</tr>');

		$PHP_OUTPUT.=('<tr>');
		$PHP_OUTPUT.=('<td colspan="2"><input type="text" name="change_title" id="InputFields" style="width:400px;"></td>');
		$PHP_OUTPUT.=('</tr>');

		$PHP_OUTPUT.=('<tr>');
		$PHP_OUTPUT.=('<td><small>Message:</small></td>');
		$PHP_OUTPUT.=('<td><small>Affected Database:</small></td>');
		$PHP_OUTPUT.=('</tr>');

		$PHP_OUTPUT.=('<tr>');
		$PHP_OUTPUT.=('<td><textarea name="change_message" id="InputFields" style="width:400px;height:50px;"></textarea></td>');
		$PHP_OUTPUT.=('<td><textarea name="affected_db" id="InputFields" style="width:200px;height:50px;"></textarea></td>');
		$PHP_OUTPUT.=('</tr>');

		$PHP_OUTPUT.=('<tr>');
		$PHP_OUTPUT.=('<td>&nbsp;</td>');
		$PHP_OUTPUT.=('<td align="right">');
		$PHP_OUTPUT.=create_submit('Add');
		$PHP_OUTPUT.=('</td>');
		$PHP_OUTPUT.=('</tr>');

	}
	$PHP_OUTPUT.=('</table>');

	$PHP_OUTPUT.=('</ul>');

}

?>