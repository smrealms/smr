<p>Balance: <b><?php echo number_format($ThisPlanet->getCredits()); ?></b></p>

<form id="BondForm" method="POST" action="<?php echo $ThisPlanet->getFinancesHREF(); ?>">
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

	<input class="BondFormSubmit" type="submit" name="action" value="Bond It!" id="InputFields" />
</form>
<div id="BondDialog" title="Confirmation required">You will be unable to access these funds until the bond matures.<br><br>Please confirm you wish to proceed.</div>
<script>
$(function(){
	$("#BondDialog").dialog({
		autoOpen: false,
		modal: true,
		height: 200,
		resizable: false,
		buttons:
			[{
				text: "Confirm",
				click: function() {
					$(this).dialog('close');
					$(".BondFormSubmit").off("click");
					$(".BondFormSubmit").click();
				}
			},
			{
				text: "Cancel",
				click: function() {
				$(this).dialog('close');
			}
			}],
		close: function() {
			$(this).dialog('close');
		}
	});
        
	$(".BondFormSubmit").on("click", function() {
		$("#BondDialog").dialog('open');
		return false;
	});
});
</script>
