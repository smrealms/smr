<?php
if ($ThisLocation->isWeaponSold()) { ?>
	<h2>Buy weapons:</h2><br />
	<table id="weapon-list" class="center standard">
		<thead>
			<tr>
				<th class="sort" data-sort="sort_name">Name</th>
				<th class="sort shrink" data-sort="sort_shield">Shield Damage</th>
				<th class="sort shrink" data-sort="sort_armor">Armour Damage</th>
				<th class="sort" data-sort="sort_acc">Accuracy</th>
				<th class="sort shrink" data-sort="sort_power">Power Level</th>
				<th class="sort" data-sort="sort_race">Seller</th>
				<th class="sort" data-sort="sort_cost">Cost</th>
				<th>Action</th>
			</tr>
		</thead>

		<tbody class="list"><?php
			foreach ($ThisLocation->getWeaponsSold() as $Weapon) { ?>
				<tr>
					<td class="sort_name"><?php echo $Weapon->getName(); ?></td>
					<td class="sort_shield"><?php echo $Weapon->getShieldDamage(); ?></td>
					<td class="sort_armor"><?php echo $Weapon->getArmourDamage(); ?></td>
					<td class="sort_acc"><?php echo $Weapon->getBaseAccuracy(); ?>%</td>
					<td class="sort_power"><?php echo $Weapon->getPowerLevel(); ?></td>
					<td class="sort_race"><?php echo $ThisPlayer->getColouredRaceName($Weapon->getRaceID(), true); ?></td>
					<td class="sort_cost"><?php echo number_format($Weapon->getCost()); ?></td>
					<td><a href="<?php echo $Weapon->getBuyHREF($ThisLocation); ?>" class="submitStyle">Buy</a></td>
				</tr><?php
			} ?>
		</tbody>
	</table>

	<?php $this->setListjsInclude('shop_weapon');
}

if ($ThisShip->hasWeapons()) { ?>
	<br /><br />
	<h2>Sell weapons:</h2><br />
	<table class="standard">
		<tr class="center">
			<th>Name</th>
			<th>Shield<br />Damage</th>
			<th>Armour<br /> Damage</th>
			<th>Accuracy</th>
			<th>Power<br />Level</th>
			<th>Resale<br />Value</th>
			<th>Action</th>
		</tr><?php
		foreach ($ThisShip->getWeapons() as $OrderID => $Weapon) { ?>
			<tr class="center">
				<td><?php echo $Weapon->getName(); ?></td>
				<td><?php echo $Weapon->getShieldDamage(); ?></td>
				<td><?php echo $Weapon->getArmourDamage(); ?></td>
				<td><?php echo $Weapon->getBaseAccuracy(); ?>%</td>
				<td><?php echo $Weapon->getPowerLevel(); ?></td>
				<td><?php echo number_format(floor($Weapon->getCost() * WEAPON_REFUND_PERCENT)); ?></td>
				<td><a href="<?php echo $Weapon->getSellHREF($ThisLocation, $OrderID); ?>" class="submitStyle">Sell</a></td>
			</tr><?php
		} ?>
	</table><?php
} ?>
