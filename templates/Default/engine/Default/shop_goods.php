<p>This is a level <?php echo $Port->getLevel(); ?> port run by the <a href="<?php echo $CouncilHREF; ?>"><?php echo $ThisPlayer->getColouredRaceName($Port->getRaceID()); ?></a>.<br />
Your relations with them are <?php echo get_colored_text($ThisPlayer->getRelation($Port->getRaceID())); ?>.</p>
<br />

<?php
if (!empty($TradeMsg)) {
	echo $TradeMsg;
}
if ($SearchedByFeds) {
	if ($IllegalsFound) { ?>
		<span class="red">
			The Federation searched your ship and illegal goods were found!<br />
			All illegal goods have been removed from your ship and you have been fined <?php echo number_format($TotalFine); ?> credits.
		</span><?php
	} else { ?>
		<span class="blue">The Federation searched your ship and no illegal goods where found!</span><?php
	} ?>
	<br /><br /><?php
}

if ($BoughtGoods) { ?>
	<h2>The port sells you the following:</h2>
	<table class="standard">
		<tr class="center">
			<th>Good</th>
			<th>Supply</th>
			<th>Base Price</th>
			<th>Amount on Ship</th>
			<th>Amount to Trade</th>
			<th>Action</th>
		</tr><?php
		foreach ($BoughtGoods as $goodID => $good) { ?>
			<form method="POST" action="<?php echo $good['HREF']; ?>">
				<tr class="center">
					<td class="left"><img src="<?php echo $good['ImageLink']; ?>" width="13" height="16" title="<?php echo $good['Name']; ?>" alt=""><?php echo $good['Name']; ?></td>
					<td class="ajax" id="amount<?php echo $goodID; ?>"><?php echo $good['PortAmount']; ?></td>
					<td><?php echo $good['BasePrice']; ?></td>
					<td><?php echo $ThisShip->getCargo($good['ID']); ?></td>
					<td><input type="number" name="amount" value="<?php echo $good['Amount']; ?>" size="4" id="InputFields" class="center"></td>
					<td>
						<input type="submit" name="action" value="Buy" id="InputFields"><?php
						if ($ThisShip->isUnderground()) { ?>
							<input type="submit" name="action" value="Steal" id="InputFields"><?php
						} ?>
					</td>
				</tr>
			</form><?php
		} ?>
	</table>
	<p>&nbsp;</p><?php
}

if ($SoldGoods) { ?>
	<h2>The port would buy the following:</h2>
	<table class="standard">
		<tr class="center">
			<th>Good</th>
			<th>Demand</th>
			<th>Base Price</th>
			<th>Amount on Ship</th>
			<th>Amount to Trade</th>
			<th>Action</th>
		</tr><?php
		foreach ($SoldGoods as $goodID => $good) { ?>
			<form method="POST" action="<?php echo $good['HREF']; ?>">
				<tr class="center">
					<td class="left"><img src="<?php echo $good['ImageLink']; ?>" width="13" height="16" title="<?php echo $good['Name']; ?>" alt=""><?php echo $good['Name']; ?></td>
					<td class="ajax" id="amount<?php echo $goodID; ?>"><?php echo $good['PortAmount']; ?></td>
					<td><?php echo $good['BasePrice']; ?></td>
					<td><?php echo $ThisShip->getCargo($goodID); ?></td>
					<td><input type="number" name="amount" value="<?php echo $good['Amount']; ?>" size="4" id="InputFields" class="center"></td>
					<td><input type="submit" name="action" value="Sell" id="InputFields"></td>
				</tr>
			</form><?php
		} ?>
	</table>
	<p>&nbsp;</p><?php
} ?>

<form method="POST" action="<?php echo $LeavePortHREF; ?>">
	<input type="submit" name="action" value="Leave Port" id="InputFields" />
</form>
