<p>Balance: <b><?php echo number_format($ThisPlanet->getCredits()); ?></b></p>

<form id="finance" method="POST" action="<?php echo $ThisPlanet->getFinancesHREF(); ?>">
	<table>
		<tr>
			<td colspan="2" align="center"><input type="number" name="amount" value="0" id="InputFields" style="text-align:right;width:152;"></td>
		</tr>
		<tr>
			<td><input type="submit" name="action" value="Deposit" id="InputFields" /></td>
			<td><input type="submit" name="action" value="Withdraw" id="InputFields" /></td>
		</tr>
	</table>

	<p>&nbsp;</p>

	<p>You are able to transfer this money into a saving bond.<br />
	It remains there for <?php echo format_time($ThisPlanet->getBondTime()); ?> and will gain <?php echo $ThisPlanet->getInterestRate() * 100; ?>% interest.<br /><br /><?php

	if ($ThisPlanet->getBonds() > 0) { ?>
		Right now there are <?php echo number_format($ThisPlanet->getBonds()); ?> credits bonded<?php
		if ($ThisPlanet->getMaturity() > 0) { ?>
			and will come to maturity in <?php echo format_time($ThisPlanet->getMaturity() - TIME);
		}
	} ?>

	</p>

	<input type="button" class="bond" name="action" value="Bond It!" id="InputFields" />
</form>
