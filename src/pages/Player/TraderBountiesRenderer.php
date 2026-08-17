<?php declare(strict_types=1);

namespace Smr\Pages\Player;

class TraderBountiesRenderer {

/**
 * @param array<array<\Smr\Bounty>> $AllClaims
 */
public static function render(array $AllClaims, string $BountyHQ, string $BountyUG): void {
?>
You have the following bounties on your head:<br /><br />
<table>
	<tr>
		<td><span class="green">Federal</span> :</td>
		<td><?php echo $BountyHQ; ?></td>
	</tr>
	<tr>
		<td><span class="red">Underground</span> :</td>
		<td><?php echo $BountyUG; ?></td>
	</tr>
</table>
<br />

<p>Bounties awaiting your collection:</p>

<table class="standard fullwidth">
	<tr>
		<th>Federal</th>
		<th>Underground</th>
	</tr>

	<tr><?php
		foreach ($AllClaims as $Claims) { ?>
			<td style="width:50%" class="top"><?php
				if (count($Claims) === 0) {
					echo 'None';
				}
				foreach ($Claims as $Claim) {
					echo $Claim->getTargetPlayer()->getLinkedDisplayName(); ?> : <span class="creds"><?php echo number_format($Claim->getCredits()); ?></span> credits and <span class="yellow"><?php echo number_format($Claim->getSmrCredits()); ?></span> SMR credits<br />
					<?php
				} ?>
			</td><?php
		} ?>
	</tr>
</table>

<?php
}

}
