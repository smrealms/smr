<?php declare(strict_types=1);

use Smr\Pages\Admin\AdminMessageSend;

?>
<form name="AdminMessageChooseGameForm" method="POST" action="<?php echo $AdminMessageChooseGameFormHref; ?>">
	<p>Please select a game:</p>
	<select name="SendGameID" size="1">
		<option value="<?php echo AdminMessageSend::ALL_GAMES_ID; ?>">Send to All Players</option><?php
		foreach ($ActiveGames as $Game) {
			?><option value="<?php echo $Game->getGameID(); ?>"><?php echo $Game->getDisplayName(); ?></option><?php
		} ?>
	</select>&nbsp;&nbsp;
	<?php echo create_submit('action', 'Select'); ?>
</form>
