<?php

function main_page() {
	// database object
	$db = new SmrMySqlDatabase();

	// list of all first letter nicks
	create_link_list();

	// news
	echo('<p><u>Space Merchant Realms Photo Album Rules</u>');
	echo('<ol>');
	echo('<li>500 x 500 pixel maximum photo size.</li>');
	echo('<li>Only .jpg, .png, or .gif files will be accepted.</li>');
	echo('<li>No derogatory or vulgar pictures will be accepted.</li>');
	echo('<li>Pictures MUST depict the real you. No anime, fictional, or otherwise \'fake\' pictures are allowed.</li>');
	echo('<li>Please watch your language while posting within the album. Same general rules apply here as in SMR chat rooms.</li>');
	echo('<li>Please respect all members in this area. Treat them as you would want to be treated. Do not post cruel or otherwise unneeded comments about someone or their property.</li>');
	echo('<li>You must be logged into your account to post within this album. Therefore, if you break any of these rules, your account may be subject to disablement.</li>');
	echo('</ol>');
	echo('<small><b>Please Note:</b> This is your only warning! All rule violations (even first time offenders) will be subject to a 1-day ban. Repeat offenders may incur longer bans.</small>');
	echo('</p>');

	echo('<p>&nbsp;</p>');

	// most hits
	echo('<p><u>Top 5 Pictures</u><br /><br />');
	$db->query('SELECT *
				FROM album
				WHERE approved = \'YES\'
				ORDER BY page_views DESC
				LIMIT 5');
	if ($db->getNumRows()) {
		while ($db->nextRecord()) {
			$page_views = $db->getField('page_views');
			$nick = get_album_nick($db->getField('account_id'));

			echo('<a href="?' . urlencode($nick) . '">'.$nick.'</a> ('.$page_views.')<br />');
		}
	}

	// latest picture
	echo('<p><u>Latest Picture</u><br /><br />');
	$db->query('SELECT *
				FROM album
				WHERE approved = \'YES\'
				ORDER BY created DESC
				LIMIT 5');
	if ($db->getNumRows()) {
		while ($db->nextRecord()) {
			$created = $db->getField('created');
			$nick = get_album_nick($db->getField('account_id'));

			echo('<span style="font-size:85%;"><b>[' . date(defined('DATE_FULL_SHORT')?DATE_FULL_SHORT:DEFAULT_DATE_FULL_SHORT, $created) . ']</b> Picture of <a href="?' . urlencode($nick) . '">'.$nick.'</a> added</span><br />');
		}
	}
	else
		echo('<span style="font-size:85%;">no entries</span>');
	echo('</p>');
}

function album_entry($album_id) {
	// database object
	$db = new SmrMySqlDatabase();

	// list of all first letter nicks
	create_link_list();

	if (SmrSession::$account_id != 0 && $album_id != SmrSession::$account_id)
		$db->query('UPDATE album
				SET page_views = page_views + 1
				WHERE account_id = '.$db->escapeNumber($album_id).' AND
					approved = \'YES\'');

	$db->query('SELECT *
				FROM album
				WHERE account_id = '.$db->escapeNumber($album_id).' AND
					approved = \'YES\'');
	if ($db->nextRecord()) {
		$location = stripslashes($db->getField('location'));
		$email = stripslashes($db->getField('email'));
		$website = stripslashes($db->getField('website'));
		$day = $db->getField('day');
		$month = $db->getField('month');
		$year = $db->getField('year');
		$other = nl2br(stripslashes($db->getField('other')));
		$page_views = $db->getField('page_views');
		$disabled = $db->getField('disabled') == 'TRUE';
	}
	else {
		echo('<h1>Error</h1>');
		echo('This user doesn\'t have an entry in our album!');
		return;
	}

	// get this user's nick
	$nick = get_album_nick($album_id);

	echo('<table border="0" align="center" cellpadding="5" cellspacing="0">');
	echo('<tr>');
	echo('<td style="text-align: center;" colspan="2">');
	echo '<div style="margin-left: auto; margin-right: auto; width: 50%">';
	echo('<table style="width: 100%">');
	echo('<tr>');

	$db->query('SELECT hof_name
				FROM album JOIN account USING(account_id)
				WHERE hof_name < ' . $db->escapeString($nick) . ' AND
					approved = \'YES\'
				ORDER BY hof_name DESC
				LIMIT 1');
	echo '<td style="text-align: center; width: 30%" valign="middle">';
	if ($db->nextRecord()) {
		$priv_nick = $db->getField('hof_name');
		echo '<a href="?' . urlencode($priv_nick) . '"><img src="/images/album/rew.jpg" alt="'.$priv_nick.'" border="0"></a>&nbsp;&nbsp;&nbsp;';
	}
	echo '</td>';
	echo('<td style="text-align: center;" valign="middle"><span style="font-size:150%;">'.$nick.'</span><br /><span style="font-size:75%;">Views: '.$page_views.'</span></td>');

	$db->query('SELECT hof_name
				FROM album JOIN account USING(account_id)
				WHERE hof_name > ' . $db->escapeString($nick) . ' AND
					approved = \'YES\'
				ORDER BY hof_name
				LIMIT 1');
	echo '<td style="text-align: center; width: 30%" valign="middle">';
	if ($db->nextRecord()) {
		$next_nick = $db->getField('hof_name');
		echo '&nbsp;&nbsp;&nbsp;<a href="?' . urlencode($next_nick) . '"><img src="/images/album/fwd.jpg" alt="'.$next_nick.'" border="0"></a>';
	}
	echo '</td>';

	echo('</tr>');
	echo('</table>');
	echo '</div>';
	echo('</td>');
	echo('</tr>');
	echo('<tr>');
	echo('<td colspan="2" align="center" valign="middle">');

	if ($disabled == false)
		echo('<img src="../upload/'.$album_id.'">');
	else
		echo('<img src="../images/album/disabled.jpg">');

	echo('</td>');
	echo('</tr>');

	if (empty($location))
		$location = 'N/A';
	echo('<tr>');
	echo('<td align="right" width="10%" style="font-weight:bold;">Location:</td><td>'.$location.'</td>');
	echo('</tr>');

	if (empty($email))
		$email = 'N/A';
	echo('<tr>');
	echo('<td align="right" width="10%" style="font-weight:bold;">eMail:</td><td>'.$email.'</td>');
	echo('</tr>');

	if (empty($website))
		$website = 'N/A';
	else
		$website = '<a href="'.$website.'" target="_new">'.$website.'</a>';
	echo('<tr>');
	echo('<td align="right" width="10%" style="font-weight:bold;">Website:</td><td>'.$website.'</td>');
	echo('</tr>');

	echo('<tr>');
	if (!empty($day) && !empty($month) && !empty($year))
		$birthdate = $month.' / '.$day.' / '.$year;
	if (empty($birthdate) && !empty($year))
		$birthdate = 'Year '.$year;
	if (empty($birthdate))
		$birthdate = 'N/A';
	echo('<td align="right" width="10%" style="font-weight:bold;">Birthdate:</td><td>'.$birthdate.'</td>');
	echo('</tr>');

	if (empty($other))
		$other = 'N/A';
	echo('<tr>');
	echo('<td align="right" valign="top" width="10%" style="font-weight:bold;">Other&nbsp;Info:<br /><small>(AIM/ICQ)&nbsp;&nbsp;</small></td><td>'.$other.'</td>');
	echo('</tr>');

	echo('<tr>');
	echo('<td colspan="2">');
	echo('<u>Comments</u><br /><br />');

	$db->query('SELECT *
				FROM album_has_comments
				WHERE album_id = '.$db->escapeNumber($album_id));
	while ($db->nextRecord()) {
		$time	= $db->getField('time');
		$postee	= get_album_nick($db->getField('post_id'));
		$msg	= stripslashes($db->getField('msg'));

		echo('<span style="font-size:85%;">[' . date(defined('DATE_FULL_SHORT')?DATE_FULL_SHORT:DEFAULT_DATE_FULL_SHORT, $time) . '] &lt;'.$postee.'&gt; '.$msg.'</span><br />');
	}

	if (SmrSession::$account_id > 0) {
		echo('<form action="album_comment.php">');
		echo('<input type="hidden" name="album_id" value="'.$album_id.'">');
		echo('<table>');
		echo('<tr>');
		echo('<td style="color:green; font-size:70%;">Nick:<br /><input type="text" size="10" name="nick" value="' . htmlspecialchars(get_album_nick(SmrSession::$account_id)) . '" class="InputFields" readonly></td>');
		echo('<td style="color:green; font-size:70%;">Comment:<br /><input type="text" size="50" name="comment" class="InputFields"></td>');
		echo('<td style="color:green; font-size:70%;"><br /><input type="submit" value="Send" class="InputFields"></td>');
		$db->query('SELECT *
					FROM account_has_permission
					WHERE account_id = '.$db->escapeNumber(SmrSession::$account_id).' AND
						permission_id = '.$db->escapeNumber(PERMISSION_MODERATE_PHOTO_ALBUM));
		if ($db->nextRecord())
			echo('<td style="color:green; font-size:70%;"><br /><input type="submit" name="action" value="Moderate" class="InputFields"></td>');

		echo('</tr>');
		echo('</table>');
		echo('</form>');
	}
	else
		echo('<p>Please <a href="/login.php?return_page=/album/?' . urlencode($nick) . '"><u>login</u></a> if you want comment on this picture!</p>');

	echo('</td>');
	echo('</tr>');
	echo('</table>');

}

function search_result($album_ids) {

	// list of all first letter nicks
	create_link_list();

	echo('<div align="center" style="font-size:125%;">Please make a selection!</div>');

	echo('<table border="0" align="center" cellpadding="5" cellspacing="0">');

	$count = 0;
	echo('<tr><td width="25%" valign="top">');

	foreach ($album_ids as $album_id) {
		$count++;

		$nick = get_album_nick($album_id);

		echo('<a href="?' . urlencode($nick) . '" style="font-size:80%;">'.$nick.'</a><br />');

		if (floor(sizeof($album_ids) / 4) > 0 && $count % floor(sizeof($album_ids) / 4) == 0)
			echo('</td><td width="25%" valign="top">');
	}
	echo('</td></tr>');
	echo('</table>');
}

function create_link_list() {
	echo('<div align="center" style="font-size:80%;">[ ');
	echo('<a href="?%">All</a> | ');
	echo('<a href="?A">A</a> | ');
	echo('<a href="?B">B</a> | ');
	echo('<a href="?C">C</a> | ');
	echo('<a href="?D">D</a> | ');
	echo('<a href="?E">E</a> | ');
	echo('<a href="?F">F</a> | ');
	echo('<a href="?G">G</a> | ');
	echo('<a href="?H">H</a> | ');
	echo('<a href="?I">I</a> | ');
	echo('<a href="?J">J</a> | ');
	echo('<a href="?K">K</a> | ');
	echo('<a href="?L">L</a> | ');
	echo('<a href="?M">M</a> | ');
	echo('<a href="?N">N</a> | ');
	echo('<a href="?O">O</a> | ');
	echo('<a href="?P">P</a> | ');
	echo('<a href="?Q">Q</a> | ');
	echo('<a href="?R">R</a> | ');
	echo('<a href="?S">S</a> | ');
	echo('<a href="?T">T</a> | ');
	echo('<a href="?U">U</a> | ');
	echo('<a href="?V">V</a> | ');
	echo('<a href="?W">W</a> | ');
	echo('<a href="?X">X</a> | ');
	echo('<a href="?Y">Y</a> | ');
	echo('<a href="?Z">Z</a> ]</div>');
	echo('<hr align="center">');
}



function get_album_nick($album_id) {
	if ($album_id == 0)
		return 'System';

	return SmrAccount::getAccount($album_id)->getHofName();
}

/*
AddHandler catch-all-handler .php
Action catch-all-handler /home/mrspock/smrealms.de/beta/www/album/index.php
*/
