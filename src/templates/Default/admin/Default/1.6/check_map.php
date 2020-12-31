<a href="<?php echo $BackHREF; ?>" class="submitStyle">&lt;&lt; Back to Map</a>
<br /><br />

<h2>Missing Locations</h2>
<?php echo join('<br />', $MissingLocNames); ?>
<br /><br />

<?php
foreach ($RouteTypes as $RouteTypeID => $RouteType) { ?>
	<h2>Top <?php echo $RouteType; ?> Routes</h2>
	<table class="standard">
		<tr>
			<td></td>
			<th>Exp</th>
			<th>Profit</th>
			<th>Route</th>
		</tr><?php
		foreach ($AllGalaxyRoutes as $GalaxyName => $GalaxyRoutes) {
			foreach ($GalaxyRoutes[$RouteTypeID] as $Routes) {
				foreach ($Routes as $Route) { ?>
				<tr>
					<th><?php echo $GalaxyName; ?></th>
					<td class="center"><?php echo round($Route->getOverallExpMultiplier(), 2); ?></td>
					<td class="center"><?php echo round($Route->getOverallMoneyMultiplier(), 2); ?></td>
					<td><?php echo nl2br($Route->getRouteString()); ?></td>
				</tr><?php
				}
			}
		} ?>
	</table><br /><?php
} ?>

<h2>Max Sell Multipliers</h2>
<table class="standard"><?php
	foreach ($MaxSellMultipliers as $GalaxyName => $MaxSellMultiplier) { ?>
		<tr>
			<th><?php echo $GalaxyName; ?></th>
			<td><?php echo $MaxSellMultiplier; ?></td>
		</tr><?php
	} ?>
</table>
