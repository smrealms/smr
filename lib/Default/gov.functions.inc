<?php


function displayBountyList(&$PHP_OUTPUT,$type,$claimableBy)
{
	$db = new SmrMySqlDatabase();
	$db->query('SELECT * FROM bounty WHERE game_id = '.SmrSession::$game_id.' AND type ='.$db->escapeString($type).' AND claimer_id = '.$claimableBy.' ORDER BY amount DESC');
	$PHP_OUTPUT.=('<p>&nbsp;</p>');
	if ($db->getNumRows())
	{
		$PHP_OUTPUT.=('<div align="center">'.($claimableBy==0?'Most Wanted by Federal Government':'Claimable Bounties').'</div><br />');
		$PHP_OUTPUT.=create_table();
		$PHP_OUTPUT.=('<tr>');
		$PHP_OUTPUT.=('<th>Player Name</th>');
		$PHP_OUTPUT.=('<th>Bounty Amount (Credits)</th>');
		$PHP_OUTPUT.=('<th>Bounty Amount (SMR credits)</th>');
		$PHP_OUTPUT.=('</tr>');
	
		while ($db->nextRecord())
		{
			$bountyPlayer =& SmrPlayer::getPlayer($db->getField('account_id'),SmrSession::$game_id);
			$PHP_OUTPUT.=('<tr>');
			$PHP_OUTPUT.=('<td align="center">'.$bountyPlayer->getLinkedDisplayName().'</td>');
			$PHP_OUTPUT.=('<td align="center"><span class="creds"> ' . number_format($db->getField('amount')) . ' </span></td>');
			$PHP_OUTPUT.=('<td align="center"><span class="red"> ' . number_format($db->getField('smr_credits')) . ' </span></td>');
			$PHP_OUTPUT.=('</tr>');
	
		}
		$PHP_OUTPUT.=('</table>');
	}
}
?>
