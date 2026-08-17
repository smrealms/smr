<?php declare(strict_types=1);

namespace Smr\Pages\Player\Headquarters;

use Smr\Pages\Shared\BountyListRenderer;

class UndergroundRenderer {

/**
 * @param array<\Smr\Bounty> $AllBounties
 * @param array<\Smr\Bounty> $MyBounties
 */
public static function render(array $AllBounties, array $MyBounties, ?string $JoinHREF): void {
?>
<p>The location appears to be abandoned, until a group of
heavily-armed figures advance from the shadows.</p>
<p>&nbsp;</p>

<?php
if (count($AllBounties) > 0) { ?>
	<div class="center">Most wanted by the Underground</div><br /><?php
	BountyListRenderer::render(Bounties: $AllBounties);
}
if (count($MyBounties) > 0) { ?>
	<div class="center">Claimable Bounties</div><br /><?php
	BountyListRenderer::render(Bounties: $MyBounties);
}

if (isset($JoinHREF)) { ?>
	<p class="center">
		<a href="<?php echo $JoinHREF; ?>" class="submitStyle">Become a smuggler</a>
	</p><?php
}

}

}
