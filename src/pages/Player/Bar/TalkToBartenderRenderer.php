<?php declare(strict_types=1);

/**
 * @var Smr\Player $ThisPlayer
 * @var string $Message
 * @var string $ProcessGossipHREF
 * @var string $ProcessTipHREF
 * @var string $BackHREF
 * @var string $ListenHREF
 */

?>
<p><?php echo $Message; ?></p>
<br />

<form method="POST" action="<?php echo $ProcessGossipHREF; ?>">
	<input type="text" name="gossip_tell" maxlength="255" size="30" />
	<?php echo create_submit_display('Spread gossip'); ?>
</form>
	<br /><br />
<form method="POST" action="<?php echo $ProcessTipHREF; ?>">
	<input type="number" name="tip" class="center" min="1" max="<?php echo $ThisPlayer->getCredits(); ?>" required />
	<?php echo create_submit_display('Give to tip jar'); ?>
</form>

<br />
<a href="<?php echo $BackHREF; ?>" class="submitStyle">Enough talk</a>
&nbsp;&nbsp;
<a href="<?php echo $ListenHREF; ?>" class="submitStyle">Keep listening</a>
