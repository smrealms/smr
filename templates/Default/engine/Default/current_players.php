<br />
<div align="center">
	<?php echo $Summary; ?>
	<br /><br />

	<div class="buttonA">
		<a class="buttonA" href="<?php echo Globals::getSendGlobalMessageHREF(); ?>">Send Global Message</a>
	</div>
	<br /><br />

	<?php
	if (!empty($AllRows)) { ?>
		<table id="cpl" class="standard" width="95%">
			<thead>
				<tr>
					<th class="sort" data-sort="sort_name">Player</th>
					<th class="sort" data-sort="sort_race">Race</th>
					<th class="sort" data-sort="sort_alliance">Alliance</th>
					<th class="sort" data-sort="sort_exp">Experience</th>
				</tr>
			</thead>

			<tbody class="list"><?php
				foreach ($AllRows as $Row) { ?>
					<tr <?php echo $Row['tr_class']; ?>>
						<td class="sort_name" data-name="<?php echo strip_tags($Row['player']->getPlayerName()); ?>" valign="top"><?php echo $Row['name_link']; ?></td>
						<td class="sort_race center">
							<?php echo $ThisPlayer->getColouredRaceName($Row['player']->getRaceID(), true); ?>
						</td>
						<td class="sort_alliance"><?php echo $Row['player']->getAllianceName(true); ?></td>
						<td class="sort_exp right"><?php echo number_format($Row['player']->getExperience()); ?></td>
					</tr><?php
				} ?>
			</tbody>
		</table>

		<script src="https://cdnjs.cloudflare.com/ajax/libs/list.js/1.5.0/list.min.js"></script>
		<script>
		var list = new List('cpl', {
			valueNames: [{name: 'sort_name', attr: 'data-name'}, 'sort_race', 'sort_alliance', 'sort_exp'],
			sortFunction: function(a, b, options) {
				return list.utils.naturalSort(a.values()[options.valueName].replace(/<.*?>|,/g,''), b.values()[options.valueName].replace(/<.*?>|,/g,''), options);
			}
		});
		</script><?php
	} ?>
</div>
