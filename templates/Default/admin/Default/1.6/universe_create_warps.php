<?php
if (isset($Message)) {
	echo $Message; ?><br /><br /><?php
} ?>

<table>
	<tr>
		<td class="shrink" style="white-space:nowrap">
			<form method="POST" action="<?php echo $SubmitHREF; ?>">
				<table class="standard">
					<tr>
						<th>Galaxy</th>
						<th>Number of Warps</th>
					</tr><?php
					foreach ($Galaxies as $eachGalaxy) { ?>
						<tr>
							<td class="right"><?php echo $eachGalaxy->getName(); ?></td>
							<td><input class="center" type="number" value="<?php echo $Warps[$Galaxy->getGalaxyID()][$eachGalaxy->getGalaxyID()]; ?>" name="warp<?php echo $eachGalaxy->getGalaxyID(); ?>"></td>
						</tr><?php
					} ?>
					<tr>
						<td colspan="2" class="center">
							<input type="submit" name="submit" value="Create Warps">
							<br /><br />
							<a href="<?php echo $CancelHREF; ?>" class="submitStyle">&lt;&lt;&nbsp;Back to Map</a>
						</td>
					</tr>
				</table>
			</form>
		</td>

		<td style="vertical-align:top">
			<div style="margin-left:10px">
				Warps will be placed randomly in the galaxies, with these exceptions:
				<ul>
					<li>Only one warp per sector</li>
				</ul>
				<span class="bold">Note:</span> When you press "Create Warps" this will rearrange all current warps.
				To add new warps without rearranging everything use the edit sector feature.
				Keep in mind this removes both sides of the warp, so 2 galaxies are changed for each warp.
			</div>
		</td>
	</tr>
</table>
<br />

<style type="text/css">
p.vert {
	writing-mode: tb-rl;
	padding: 1px;
	margin: 1px;
}
</style>

<h2>Warp Summary</h2>
<p>Click on a galaxy name to edit warps for that galaxy.</p>
<table class="standard">
	<tr>
		<td></td><?php
		foreach ($Galaxies as $gal) { ?>
			<th>
				<p class="vert">
					<a href="<?php echo $GalLinks[$gal->getGalaxyID()]; ?>"><?php echo $gal->getName(); ?></a>
				</p>
			</th><?php
		} ?>
		<th><p class="vert">Total</p></th>
	</tr><?php
	foreach ($Galaxies as $galRow) { ?>
		<tr>
			<th><a href="<?php echo $GalLinks[$galRow->getGalaxyID()]; ?>"><?php echo $galRow->getName(); ?></a></th><?php
			foreach ($Galaxies as $galCol) {
				$count = $Warps[$galRow->getGalaxyID()][$galCol->getGalaxyID()];
				$display = $count == 0 ? '' : $count; ?>
				<td class="center"><?php echo $display; ?></td><?php
			} ?>
			<th><?php echo array_sum($Warps[$galRow->getGalaxyID()]); ?></th>
		</tr><?php
	} ?>
</table>
