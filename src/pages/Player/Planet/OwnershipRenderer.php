<?php declare(strict_types=1);

namespace Smr\Pages\Player\Planet;

use Smr\Planet;
use Smr\Player;

class OwnershipRenderer {

	public static function render(OwnershipProcessor $ProcessingPage, Planet $Planet, ?int $PlayerPlanet, Player $ThisPlayer): void {
		$ProcessingHREF = $ProcessingPage->href();
		if (!$Planet->hasOwner()) { ?>
			<p>
				This planet is unclaimed.<?php
				if ($PlayerPlanet !== null) { ?>
					<br />If you claim it, you will lose ownership of the planet in Sector #<?php echo $PlayerPlanet; ?>!<?php
				} ?>
			</p>
			<form method="POST" action="<?php echo $ProcessingHREF; ?>">
				<input hidden name="password" />
				<?php echo $ProcessingPage->actionTakeOwnership->html(); ?>
			</form><?php
		} else {
			if ($Planet->getOwnerID() !== $ThisPlayer->getAccountID()) { ?>
				<p><?php echo Player::getPlayer($Planet->getOwnerID(), $Planet->getGameID())->getLinkedDisplayName(false); ?> owns this planet.</p>
				<p>
					You can claim the planet when you enter the correct password.<?php
					if ($PlayerPlanet !== null) { ?>
						<br />If you do, you will lose ownership of the planet in Sector #<?php echo $PlayerPlanet; ?>!<?php
					} ?>
				</p>
				<form method="POST" action="<?php echo $ProcessingHREF; ?>">
					<input type="text" name="password">&nbsp;&nbsp;&nbsp;
					<?php echo $ProcessingPage->actionTakeOwnership->html(); ?>
				</form><?php
			} else { ?>
				<p>You own this planet!</p>
				<form method="POST" action="<?php echo $ProcessingHREF; ?>">
					<input type="text" name="password" maxlength="32" value="<?php echo htmlspecialchars($Planet->getPassword()); ?>" />&nbsp;&nbsp;&nbsp;
					<?php echo $ProcessingPage->actionSetPassword->html(); ?>
				</form>
				<br />

				<form method="POST" action="<?php echo $ProcessingHREF; ?>">
					<input required type="text" name="name" maxlength="32" value="<?php echo $Planet->getDisplayName(); ?>" />&nbsp;&nbsp;&nbsp;
					<?php echo $ProcessingPage->actionRename->html(); ?>
				</form><?php
			}
		}

	}

}
