<?php
if ($ThisLocation->isWeaponSold()) { ?>
	<table id="weapon-list" class="center standard">
		<thead>
			<tr>
				<th class="sort" data-sort="sort_name">Name</th>
				<th class="sort" data-sort="sort_shield">Shield Damage</th>
				<th class="sort" data-sort="sort_armor">Armour Damage</th>
				<th class="sort" data-sort="sort_acc">Accuracy</th>
				<th class="sort" data-sort="sort_race">Race</th>
				<th class="sort" data-sort="sort_power">Power Level</th>
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
					<td class="sort_acc"><?php echo $Weapon->getBaseAccuracy(); ?></td>
					<td class="sort_race"><?php echo $Weapon->getRaceName(); ?></td>
					<td class="sort_power"><?php echo $Weapon->getPowerLevel(); ?></td>
					<td class="sort_cost"><?php echo $Weapon->getCost(); ?></td>
					<td><a href="<?php echo $Weapon->getBuyHREF($ThisLocation); ?>" class="submitStyle">Buy</a></td>
					</td>
				</tr><?php
			} ?>
		</tbody>
	</table>

	<script src="https://cdnjs.cloudflare.com/ajax/libs/list.js/1.5.0/list.min.js"></script>
	<script>
	var list = new List('weapon-list', {
		valueNames: ['sort_name', 'sort_shield', 'sort_armor', 'sort_acc', 'sort_race', 'sort_power', 'sort_cost'],
		sortFunction: function(a, b, options) {
			return list.utils.naturalSort(a.values()[options.valueName].replace(/<.*?>|,/g,''), b.values()[options.valueName].replace(/<.*?>|,/g,''), options);
		}
	});
	</script><?php
}

if ($ThisShip->hasWeapons()) { ?>
	<br /><br />
	<h1>Sell Weapons</h1><br />

	<table class="standard">
		<tr class="center">
			<th>Name</th>
			<th>Cash</th>
			<th>Action</th>
		</tr><?php
		foreach ($ThisShip->getWeapons() as $OrderID => $Weapon) { ?>
			<tr class="center">
				<td><?php echo $Weapon->getName(); ?></td>
				<td><?php echo number_format(floor($Weapon->getCost() * WEAPON_REFUND_PERCENT)); ?></td>
				<td><a href="<?php echo $Weapon->getSellHREF($ThisLocation, $OrderID); ?>" class="submitStyle">Sell</a></td>
			</tr><?php
		} ?>
	</table><?php
} ?>
