<?

$bounties = 0;
$id = $var['id'];
$curr_player =& SmrPlayer::getPlayer($id, $player->getGameID());
$smarty->assign('PageTopic','Viewing '.$curr_player->getPlayerName());
$db->query('SELECT * FROM bounty WHERE account_id = '.$id.' AND game_id = '.$player->getGameID().' AND type = ' . $db->escape_string('HQ'));
while ($db->next_record()) {

    $claimer = $db->f('claimer_id');
	//$days = (TIME - $db->f('time')) / 60 / 60 / 24;
    //$amount = round($db->f('amount') * pow(1.05,$days));
    $amount = $db->f('amount');
    $PHP_OUTPUT.=('The <font color=green>Federal Government</font> is offering a bounty on '.$curr_player->getPlayerName().' worth <font color=yellow>'.$amount.'</font> credits.<br>  ');
    if ($claimer != 0) {

        $claiming_player =& SmrPlayer::getPlayer($claimer, $player->getGameID());
        $PHP_OUTPUT.=('This bounty can be claimed by '.$claiming_player->getPlayerName().'<br>');

    }
    $bounties += 1;
	
}
if ($bounties > 0) $PHP_OUTPUT.=('<br><br><br>');


$db->query('SELECT * FROM bounty WHERE account_id = '.$id.' AND game_id = '.$player->getGameID().' AND type = ' . $db->escape_string('UG'));

while ($db->next_record()) {

    $claimer = $db->f('claimer_id');
	//$days = (TIME - $db->f('time')) / 60 / 60 / 24;
    //$amount = round($db->f('amount') * pow(1.05,$days));
    $amount = $db->f('amount');
    $PHP_OUTPUT.=('The <font color=red>Underground</font> is offering a bounty on '.$curr_player->getPlayerName().' worth <font color=yellow>'.$amount.'</font> credits.<br>');
    if ($claimer != 0) {

        $claiming_player =& SmrPlayer::getPlayer($claimer, $player->getGameID());
        $PHP_OUTPUT.=('This bounty can be claimed by '.$claiming_player->getPlayerName().'<br>');

    }
	$bounties += 1;
}

if ($bounties == 0)
	$PHP_OUTPUT.=('This player has no bounties<br>');
?>