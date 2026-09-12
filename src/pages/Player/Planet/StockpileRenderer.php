<?php declare(strict_types=1);

namespace Smr\Pages\Player\Planet;

class StockpileRenderer {

	/**
	 * @param array<int, array{Name: string, ImageHTML: string, ShipAmount: int, PlanetAmount: int, DefaultAmount: int, Page: \Smr\Pages\Player\Planet\StockpileProcessor}> $GoodInfo
	 */
	public static function render(array $GoodInfo): void {
		if (count($GoodInfo) === 0) { ?>
			<p>There are no goods present on your ship or the planet!</p><?php
			return;
		} ?>

		<br />
		<table class="standard">
			<tr>
				<th></th>
				<th>Good</th>
				<th>Ship</th>
				<th>Planet</th>
				<th>Amount</th>
				<th>Transfer To</th>
			</tr>

			<?php
			foreach ($GoodInfo as $goodID => $info) {
				$formID = 'stockpile-' . $goodID; ?>
				<tr>
					<td class="left"><?php echo $info['ImageHTML']; ?></td>
					<td><?php echo $info['Name']; ?></td>
					<td class="center"><?php echo $info['ShipAmount']; ?></td>
					<td class="center"><?php echo $info['PlanetAmount']; ?></td>
					<td><input form="<?php echo $formID; ?>" type="number" name="amount" value="<?php echo $info['DefaultAmount']; ?>" class="center" size="4" /></td>
					<td class="center">
						<form id="<?php echo $formID; ?>" method="POST" action="<?php echo $info['Page']->href(); ?>">
							<?php echo $info['Page']->actionShip->html(); ?>&thinsp;
							<?php echo $info['Page']->actionPlanet->html(); ?>
						</form>
					</td>
				</tr><?php
			} ?>
		</table>

		<?php
	}

}
