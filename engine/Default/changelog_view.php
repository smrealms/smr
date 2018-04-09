<?php

$template->assign('PageTopic','Change Log');

if(isset($var['Since'])) {
	$PHP_OUTPUT.=create_button(create_container('logged_in.php'), 'Continue');
	$PHP_OUTPUT.='<br /><br />';
	$PHP_OUTPUT.='<big>Here are the updates that have gone live since your last visit, enjoy!</big><br/><br/>';

	// Make the changelog scroll if it is larger than 420px
	$PHP_OUTPUT.='<style>div.login_scroll {height: 420px; overflow-y: auto;}</style>';
}

$db2 = new SmrMySqlDatabase();

// login_scroll class is only defined when this page is viewed during login
$PHP_OUTPUT.='<div class="login_scroll">';
$db->query('SELECT *
			FROM version
			WHERE went_live > ' . (isset($var['Since']) ? $db->escapeNumber($var['Since']) : '0') . '
			ORDER BY version_id DESC');
while ($db->nextRecord()) {
	$version_id = $db->getInt('version_id');
	$version = $db->getInt('major_version') . '.' . $db->getInt('minor_version') . '.' . $db->getInt('patch_level');
	$went_live = $db->getInt('went_live');

	// get human readable format for date
	if ($went_live > 0) {
		$went_live = date(DATE_FULL_SHORT, $went_live);
	}
	else {
		$went_live = 'never';
	}

	$PHP_OUTPUT.=('<b>'.$version.' ('.$went_live.'):</b>');

	$PHP_OUTPUT.=('<ul>');

	$db2->query('SELECT *
				FROM changelog
				WHERE version_id = ' . $db2->escapeNumber($version_id) . '
				ORDER BY changelog_id');
	while ($db2->nextRecord()) {
		$PHP_OUTPUT.=('<li><span style="font-size:125%;color:greenyellow;">' . $db2->getField('change_title') . '</span><br />' . $db2->getField('change_message') . '<br /><br /></li>');
	}

	$PHP_OUTPUT.=('</ul><br />');
}
$PHP_OUTPUT.='</div>';
