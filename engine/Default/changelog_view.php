<?php

$smarty->assign('PageTopic','Change Log');

$db2 = new SMR_DB();

$db->query('SELECT *
			FROM version
			WHERE version_id <= ' . $var['version_id'] . '
			ORDER BY version_id DESC');
while ($db->next_record()) {

	$version_id = $db->f('version_id');
	$version = $db->f('major_version') . '.' . $db->f('minor_version') . '.' . $db->f('patch_level');
	$went_live = $db->f('went_live');

	// get human readable format for date
	if ($went_live > 0)
		$went_live = date('m/d/Y - h:i A', $went_live);
	else
		$went_live = 'never';

	$PHP_OUTPUT.=('<b><small>'.$version.' ('.$went_live.'):</small></b>');

	$PHP_OUTPUT.=('<ul>');

	$db2->query('SELECT *
				FROM changelog
				WHERE version_id = '.$version_id.'
				ORDER BY changelog_id');
	while ($db2->next_record())
		$PHP_OUTPUT.=('<li>' . stripslashes($db2->f('change_title')) . '<br /><small>' . stripslashes($db2->f('change_message')) . '</small></li>');

	$PHP_OUTPUT.=('</ul>');

}

?>