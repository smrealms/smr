<p><?php echo $Message; ?></p>
<br />

<form method="POST" action="<?php echo $GossipHREF; ?>">
	<input type="text" name="gossip_tell" size="30" class="InputFields" />
	<input type="submit" name="action" value="Tell him" />
</form>

<br />
<a href="<?php echo Globals::getBarMainHREF(); ?>" class="submitStyle">Enough talk</a>
&nbsp;&nbsp;
<a href="<?php echo $ListenHREF; ?>" class="submitStyle">Keep listening</a>
