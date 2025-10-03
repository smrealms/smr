<?php declare(strict_types=1);

use Smr\Player;

/**
 * @var Smr\Planet $Planet
 * @var Smr\Player $ThisPlayer
 * @var string $ProcessingHREF
 */

if (!$Planet->hasOwner()) { ?>
	<p>
		This planet is unclaimed.<?php
		if (isset($PlayerPlanet)) { ?>
			<br />If you claim it, you will lose ownership of the planet in Sector #<?php echo $PlayerPlanet; ?>!<?php
		} ?>
	</p>
	<form method="POST" action="<?php echo $ProcessingHREF; ?>">
		<input hidden name="password" />
		<?php echo create_submit('action', 'Take Ownership'); ?>
	</form><?php
} else {
	if ($Planet->getOwnerID() !== $ThisPlayer->getAccountID()) { ?>
		<p><?php echo Player::getPlayer($Planet->getOwnerID(), $Planet->getGameID())->getLinkedDisplayName(false); ?> owns this planet.</p>
		<p>
			You can claim the planet when you enter the correct password.<?php
			if (isset($PlayerPlanet)) { ?>
				<br />If you do, you will lose ownership of the planet in Sector #<?php echo $PlayerPlanet; ?>!<?php
			} ?>
		</p>
		<form method="POST" action="<?php echo $ProcessingHREF; ?>">
			<input type="text" name="password">&nbsp;&nbsp;&nbsp;
			<?php echo create_submit('action', 'Take Ownership'); ?>
		</form><?php
	} else { ?>
		<p>You own this planet!</p>
		<form method="POST" action="<?php echo $ProcessingHREF; ?>">
			<input type="text" name="password" maxlength="32" value="<?php echo htmlspecialchars($Planet->getPassword()); ?>" />&nbsp;&nbsp;&nbsp;
			<?php echo create_submit('action', 'Set Password'); ?>
		</form>
		<br />

		<form method="POST" action="<?php echo $ProcessingHREF; ?>">
			<input required type="text" name="name" maxlength="32" value="<?php echo $Planet->getDisplayName(); ?>" />&nbsp;&nbsp;&nbsp;
			<?php echo create_submit('action', 'Rename'); ?>
		</form><?php
	}
}
