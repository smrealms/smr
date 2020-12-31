<h2>Planetary Bond Confirmation</h2>

<p>All credits on the planet at the time of confirmation, along with any
credits currently bonded (and any partial interest they may have accrued),
will be added to a new bond. You will not be able to access these funds until
the bond matures in <?php echo $BondDuration; ?>.</p>

<p>Please confirm to proceed.</p>

<form id="BondConfirmForm" method="POST" action="<?php echo $ReturnHREF; ?>">
	<table>
		<tr>
			<td><input type="submit" name="action" value="Confirm" id="confirmBond" /></td>
			<td><input type="submit" name="action" value="Cancel" id="cancelBond" /></td>
		</tr>
	</table>
</form>
