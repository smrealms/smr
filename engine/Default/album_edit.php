<?php
$template->assign('PageTopic','Edit Photo');

$db->query('SELECT * FROM album WHERE account_id = '.SmrSession::$account_id);
if ($db->nextRecord())
{
	$albumEntry['Location'] = stripslashes($db->getField('location'));
	$albumEntry['Email'] = stripslashes($db->getField('email'));
	$albumEntry['Website'] = stripslashes($db->getField('website'));
	$albumEntry['Day'] = $db->getField('day');
	$albumEntry['Month'] = $db->getField('month');
	$albumEntry['Year'] = $db->getField('year');
	$albumEntry['Other'] = stripslashes($db->getField('other'));
	$approved = $db->getField('approved');

	if ($approved == 'TBC')
		$albumEntry['Status']=('<span style="color:orange;">Waiting approval</span>');
	elseif ($approved == 'NO')
		$albumEntry['Status']=('<span class="red">Approval denied</span>');
	elseif ($db->getField('disabled') == 'TRUE')
		$albumEntry['Status']=('<span class="red">Disabled</span>');
	elseif ($approved == 'YES')
		$albumEntry['Status']=('<a href="'.URL.'/album/?'.$account->getHofName().'" class="dgreen">Online</a>');
		
	if(is_readable(UPLOAD . SmrSession::$account_id))
		$albumEntry['Image'] = URL.'/upload/'.SmrSession::$account_id;
	
	$template->assign('AlbumEntry',$albumEntry);
}

$template->assign('AlbumEditHref',SmrSession::get_new_href(create_container('album_edit_processing.php', '')))
?>