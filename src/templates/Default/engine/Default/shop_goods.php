<?php declare(strict_types=1);

use Smr\TransactionType;

/**
 * @var Smr\Player $ThisPlayer
 * @var Smr\Port $Port
 * @var Smr\Ship $ThisShip
 * @var array<int, array{HREF: string, Image: string, Name: string, BasePrice: int, PortAmount: int, Amount: int}> $BoughtGoods
 * @var array<int, array{HREF: string, Image: string, Name: string, BasePrice: int, PortAmount: int, Amount: int}> $SoldGoods
 * @var bool $SearchedByFeds
 * @var ?int $TotalFine
 * @var string $LeavePortHREF
 */

?>
<p>This is a level <?php echo $Port->getLevel(); ?> port run by the <?php echo $ThisPlayer->getColouredRaceName($Port->getRaceID(), true); ?>.<br />
Your relations with them are <?php echo get_colored_text($ThisPlayer->getRelation($Port->getRaceID())); ?>.</p>

<?php
if (!empty($TradeMsg)) { ?>
	<p><?php echo $TradeMsg; ?></p><?php
}

if ($SearchedByFeds) { ?>
	<p><?php
		if (isset($TotalFine)) { ?>
			<span class="red">
				The Federation searched your ship and illegal goods were found!<br />
				All illegal goods have been removed from your ship and you have been fined <?php echo number_format($TotalFine); ?> credits.
			</span><?php
		} else { ?>
			<span class="blue">The Federation searched your ship and no illegal goods where found!</span><?php
		} ?>
	</p><?php
} ?>

<br />
<?php
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
			<tr class="center">
				<td class="left"><span class="pad1"><?php echo $good['Image']; ?></span>&nbsp;<?php echo $good['Name']; ?></td>
				<td class="ajax" id="amount<?php echo $goodID; ?>"><?php echo $good['PortAmount']; ?></td>
				<td><?php echo $good['BasePrice']; ?></td>
				<td><?php echo $ThisShip->getCargo($goodID); ?></td>
				<td><input form="form<?php echo $goodID; ?>" type="number" name="amount" value="<?php echo $good['Amount']; ?>" required min="1" size="4" class="center"></td>
				<td>
					<form id="form<?php echo $goodID; ?>" method="POST" action="<?php echo $good['HREF']; ?>">
						<input type="submit" name="action" value="<?php echo TransactionType::Buy->value; ?>"><?php
						if ($ThisShip->isUnderground()) { ?>
							<input type="submit" name="action" value="<?php echo TransactionType::STEAL; ?>"><?php
						} ?>
					</form>
				</td>
			</tr><?php
		} ?>
	</table>
	<br /><br /><?php
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
			<tr class="center">
				<td class="left"><span class="pad1"><?php echo $good['Image']; ?></span>&nbsp;<?php echo $good['Name']; ?></td>
				<td class="ajax" id="amount<?php echo $goodID; ?>"><?php echo $good['PortAmount']; ?></td>
				<td><?php echo $good['BasePrice']; ?></td>
				<td><?php echo $ThisShip->getCargo($goodID); ?></td>
				<td><input form="form<?php echo $goodID; ?>" type="number" name="amount" value="<?php echo $good['Amount']; ?>" required min="1" size="4" class="center"></td>
				<td>
					<form id="form<?php echo $goodID; ?>" method="POST" action="<?php echo $good['HREF']; ?>">
						<input type="submit" name="action" value="<?php echo TransactionType::Sell->value; ?>">
					</form>
				</td>
			</tr><?php
		} ?>
	</table>
	<br /><br /><?php
} ?>

<a href="<?php echo $LeavePortHREF; ?>" class="submitStyle">Leave Port</a>
