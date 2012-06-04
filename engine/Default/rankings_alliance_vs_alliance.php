<?
require_once(get_file_loc('smr_alliance.inc'));
$template->assign('PageTopic','ALLIANCE VS ALLIANCE RANKINGS');

include(get_file_loc('menue.inc'));
$PHP_OUTPUT.=create_ranking_menue(1, 3);
$db2 = new SmrMySqlDatabase();
$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'rankings_alliance_vs_alliance.php';

$PHP_OUTPUT.=create_echo_form($container);

if (isset($_POST['alliancer'])) $alliancer = $_POST['alliancer'];
$PHP_OUTPUT.=('<div align="center">');
$PHP_OUTPUT.=('<p>Here are the rankings of alliances vs other alliances<br />');
$PHP_OUTPUT.=('Click on an alliances name for more detailed death stats.</p>');

$PHP_OUTPUT.=('<table class="standard" width="95%">');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<th rowspan="9">Killed</th><th colspan="8">Killers</th></tr><tr><td>&nbsp</td>');
if (empty($alliancer)) {
	
	$alliance_vs = array();
	$db->query('SELECT * FROM alliance WHERE game_id = '.$player->getGameID().' ORDER BY alliance_kills DESC, alliance_name LIMIT 5');
	while ($db->nextRecord()) $alliance_vs[] = $db->getField('alliance_id');
	//$PHP_OUTPUT.=('empty '.$alliancer);
	
} else $alliance_vs = $alliancer;
$alliance_vs[] = 0;

foreach ($alliance_vs as $key => $id) {
	
	// get current alliance
	$curr_alliance_id = $id;
	if ($id > 0) {
		
	    $curr_alliance = new SMR_ALLIANCE($id, SmrSession::$game_id);
		$db2->query('SELECT * FROM player WHERE alliance_id = '.$id.' AND game_id = '.SmrSession::$game_id);
		if ($db2->getNumRows() == 0) $out = TRUE;
		else $out = FALSE;
		
		$PHP_OUTPUT.=('<td width=15% valign="top"');
		if ($player->getAllianceID() == $curr_alliance_id)
		$PHP_OUTPUT.=(' style="font-weight:bold;"');
		$PHP_OUTPUT.=('>');
		/*$container = array();
		$container['url']             = 'skeleton.php';
		$container['body']             = 'alliance_roster.php';
		$container['alliance_id']    = $curr_alliance_id;
		$PHP_OUTPUT.=create_link($container, '.$db->escapeString($curr_alliance->alliance_name');*/
		$PHP_OUTPUT.=('<select name=alliancer[] style=width:105>');
		$db->query('SELECT * FROM alliance WHERE game_id = '.$player->getGameID().' AND alliance_deaths > 0 OR alliance_kills > 0 ORDER BY alliance_name');
		while ($db->nextRecord()) {
			
			$curr_alliance = new SMR_ALLIANCE($db->getField('alliance_id'), SmrSession::$game_id);
			$PHP_OUTPUT.=('<option value=' . $db->getField('alliance_id'));
			if ($id == $db->getField('alliance_id'))
				$PHP_OUTPUT.=(' selected');
			$PHP_OUTPUT.=('>' . $curr_alliance->alliance_name . '</option>');
			
		}
		$PHP_OUTPUT.=($curr_alliance->alliance_name);
		$PHP_OUTPUT.=('</td>');
		
	}
	//$alliance_vs[] = $curr_alliance_id;

}
$PHP_OUTPUT.=('<td width=10% valign="top">None</td>');
$PHP_OUTPUT.=('</tr>');
//$db->query('SELECT * FROM alliance WHERE game_id = '.$player->getGameID().' ORDER BY alliance_kills DESC, alliance_name LIMIT 5');
foreach ($alliance_vs as $key => $id) {
	
	$PHP_OUTPUT.=('<tr>');
	// get current alliance
	$curr_id = $id;
	if ($id > 0) {
		
		$curr_alliance = new SMR_ALLIANCE($id, SmrSession::$game_id);
		$db2->query('SELECT * FROM player WHERE alliance_id = '.$curr_id.' AND game_id = '.SmrSession::$game_id);
		if ($db2->getNumRows() == 0) $out = TRUE;
		else $out = FALSE;
		
		$PHP_OUTPUT.=('<td width=10% valign="top"');
		if ($player->getAllianceID() == $curr_alliance->alliance_id)
			$PHP_OUTPUT.=(' style="font-weight:bold;"');
		if ($out)
			$PHP_OUTPUT.=(' style="color:red;"');
		$PHP_OUTPUT.=('>');
		$container1 = array();
		$container1['url']            = 'skeleton.php';
		$container1['body']           = 'rankings_alliance_vs_alliance.php';
		$container1['alliance_id']    = $curr_alliance->alliance_id;
		$PHP_OUTPUT.=create_link($container1, $curr_alliance->alliance_name);
		//$PHP_OUTPUT.=('.$db->escapeString($curr_alliance->alliance_name');
		$PHP_OUTPUT.=('</td>');
		
	} else {
		
		$container1 = array();
		$container1['url']            = 'skeleton.php';
		$container1['body']           = 'rankings_alliance_vs_alliance.php';
		$container1['alliance_id']    = 0;
		$PHP_OUTPUT.=('<td width=10% valign="top">');
		$PHP_OUTPUT.=create_link($container1, 'None');
		$PHP_OUTPUT.=('</td>');
		
	}
	
	foreach ($alliance_vs as $key => $id) {
		
		$db2->query('SELECT * FROM player WHERE alliance_id = '.$id.' AND game_id = '.SmrSession::$game_id);
		if ($db2->getNumRows() == 0) $out2 = TRUE;
		else $out2 = FALSE;
		$db2->query('SELECT * FROM alliance_vs_alliance WHERE alliance_id_2 = '.$curr_id.' AND ' .
					'alliance_id_1 = '.$id.' AND game_id = '.$player->getGameID());
		if ($curr_id == $id && $id != 0) {
			
			if (($out || $out2))
				$PHP_OUTPUT.=('<td style="color:red;">-');
			elseif ($id == $player->getAllianceID() || $curr_id == $player->getAllianceID())
				$PHP_OUTPUT.=('<td style="font-weight:bold;">-');
			else $PHP_OUTPUT.=('<td>-');
			
		} elseif ($db2->nextRecord()) {
			
			$PHP_OUTPUT.=('<td');
			if (($out || $out2) && ($id == $player->getAllianceID() || $curr_id == $player->getAllianceID()))
				$PHP_OUTPUT.=(' style="font-weight:bold;color:red;"');
			elseif ($out || $out2)
				$PHP_OUTPUT.=(' style="color:red;"');
			elseif ($id == $player->getAllianceID() || $curr_id == $player->getAllianceID()) $PHP_OUTPUT.=(' style="font-weight:bold;"');
			$PHP_OUTPUT.=('>');
			$PHP_OUTPUT.= $db2->getField('kills');
			
		} else {
			
			$PHP_OUTPUT.=('<td');
			if (($out || $out2) && ($id == $player->getAllianceID() || $curr_id == $player->getAllianceID()))
				$PHP_OUTPUT.=(' style="font-weight:bold;color:red;"');
			elseif ($out || $out2)
				$PHP_OUTPUT.=(' style="color:red;"');
			elseif ($id == $player->getAllianceID() || $curr_id == $player->getAllianceID()) $PHP_OUTPUT.=(' style="font-weight:bold;"');
			$PHP_OUTPUT.=('>');
			$PHP_OUTPUT.=('0');
			
		}
		$PHP_OUTPUT.=('</td>');
		
	}

    $PHP_OUTPUT.=('</tr>');

}

$PHP_OUTPUT.=('</table>');

$PHP_OUTPUT.=('<br />');
$PHP_OUTPUT.=create_submit('Show');
$PHP_OUTPUT.=('</form>');
$PHP_OUTPUT.=('</div>');

if (isset($var['alliance_id'])) {
	
	$PHP_OUTPUT.=('<table align="center"><tr><td width="45%" align="center" valign="top">');
	$main_alliance = new SMR_ALLIANCE($var['alliance_id'], SmrSession::$game_id);
	$db->query('SELECT * FROM alliance_vs_alliance WHERE alliance_id_1 = '.$var['alliance_id'] .
				' AND game_id = '.$player->getGameID().' ORDER BY kills DESC');
	if ($db->getNumRows() > 0) {
		
		$PHP_OUTPUT.=('<div align="center">Kills for '.$main_alliance->alliance_name);
		$PHP_OUTPUT.=('<table class="standard"><tr><th align=center>Alliance Name</th>');
		$PHP_OUTPUT.=('<th align="center">Amount</th></tr>');
		while ($db->nextRecord()) {
			
			$kills = $db->getField('kills');
			$id = $db->getField('alliance_id_2');
			if ($id > 0) {
				
				$killer_alliance = new SMR_ALLIANCE($id, SmrSession::$game_id);
				$alliance_name = $killer_alliance->alliance_name;
			
			} elseif ($id == 0) $alliance_name = '<font color="blue">No Alliance</font>';
			elseif ($id == -1) $alliance_name = '<font color="blue">Forces</font>';
			elseif ($id == -2) $alliance_name = '<font color="blue">Planets</font>';
			elseif ($id == -3) $alliance_name = '<font color="blue">Ports</font>';
			
			$PHP_OUTPUT.=('<tr><td align="center">'.$alliance_name.'</td><td align="center">'.$kills.'</td></tr>');
			
		}
		$PHP_OUTPUT.=('</table>');
		
	} else $PHP_OUTPUT.=($main_alliance->alliance_name.' has no kills!');
	$PHP_OUTPUT.=('</td><td width="10%">&nbsp;</td><td width="45%" align="center" valign="top">');
	$db->query('SELECT * FROM alliance_vs_alliance WHERE alliance_id_2 = '.$var['alliance_id'] .
				' AND game_id = '.$player->getGameID().' ORDER BY kills DESC');
	if ($db->getNumRows() > 0) {
		
		$PHP_OUTPUT.=('<div align="center">Deaths for '.$main_alliance->alliance_name);
		$PHP_OUTPUT.=('<table class="standard"><tr><th align=center>Alliance Name</th>');
		$PHP_OUTPUT.=('<th align="center">Amount</th></tr>');
		while ($db->nextRecord()) {
			
			$kills = $db->getField('kills');
			$id = $db->getField('alliance_id_1');
			if ($id > 0) {
				
				$killer_alliance = new SMR_ALLIANCE($id, SmrSession::$game_id);
				$alliance_name = $killer_alliance->alliance_name;
			
			} elseif ($id == 0) $alliance_name = '<font color="blue">No Alliance</font>';
			elseif ($id == -1) $alliance_name = '<font color="blue">Forces</font>';
			elseif ($id == -2) $alliance_name = '<font color="blue">Planets</font>';
			elseif ($id == -3) $alliance_name = '<font color="blue">Ports</font>';
			
			$PHP_OUTPUT.=('<tr><td align="center">'.$alliance_name.'</td><td align="center">'.$kills.'</td></tr>');
			
		}
		$PHP_OUTPUT.=('</table>');
		
	} else $PHP_OUTPUT.=($main_alliance->alliance_name.' has no deaths!');
	$PHP_OUTPUT.=('</td></tr></table>');
}		

?>