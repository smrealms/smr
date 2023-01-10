<?php declare(strict_types=1);

?>
<p><?php echo $Message; ?></p>
<br />

<form method="POST" action="<?php echo $ProcessingHREF; ?>">
	<input type="text" name="gossip_tell" size="30" />
	<button type="submit" name="action" value="tell">Spread gossip</button>
</form>
	<br /><br />
<form method="POST" action="<?php echo $ProcessingHREF; ?>">
	<input type="number" name="tip" class="center" min="1" max="<?php echo $ThisPlayer->getCredits(); ?>" required />
	<button type="submit" name="action" value="tip">Give to tip jar</button>
</form>

<br />
<a href="<?php echo $BackHREF; ?>" class="submitStyle">Enough talk</a>
&nbsp;&nbsp;
<a href="<?php echo $ListenHREF; ?>" class="submitStyle">Keep listening</a>
