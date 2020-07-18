<p>Balance: <b><span id="planet_credits"><?php echo number_format($ThisPlanet->getCredits()); ?></span></b></p>

<form id="BondForm" method="POST" action="<?php echo $ThisPlanet->getFinancesHREF(); ?>">
	<table>
		<tr>
			<td colspan="2"><input type="number" name="amount" value="0" style="text-align:right;width:152;"></td>
		</tr>
		<tr>
			<td><input type="submit" name="action" value="Deposit" /></td>
			<td><input type="submit" name="action" value="Withdraw" /></td>
		</tr>
	</table>
</form>

	<p>&nbsp;</p> <?php

// Print bond properties if the planet is claimed
if (!$ThisPlanet->isClaimed()) {
	echo "This planet must be claimed before you can bond funds here.<br /><br />";
} else { ?>
	You are able to transfer these credits into a planetary bond.<br />
	The credits will remain bonded for <?php echo format_time($ThisPlanet->getBondTime()); ?> and will gain <?php echo $ThisPlanet->getInterestRate() * 100; ?>% interest.<br /><br /><?php
} ?>

<span id="planet_bond"><?php
	// Always display the bond status if there is a bond
	if ($ThisPlanet->getBonds() > 0) { ?>
		Right now there are <?php echo number_format($ThisPlanet->getBonds()); ?> credits bonded<?php
		if ($ThisPlanet->getMaturity() > 0) { ?>
			and will come to maturity in <?php echo format_time($ThisPlanet->getMaturity() - TIME); ?>.
			<br /><br /> <?php
		}
	} ?>
</span><?php

// Allow the player to bond if the planet is claimed
if ($ThisPlanet->isClaimed()) { ?>
	<div class="buttonA">
		<a id="bondFunds" class="buttonA" href="<?php echo $ThisPlanet->getBondConfirmationHREF(); ?>">Bond Funds</a>
	</div>&nbsp; <?php
} ?>
