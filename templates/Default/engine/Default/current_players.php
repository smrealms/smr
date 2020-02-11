<br />
<div class="center">
	<?php echo $Summary; ?>
	<br /><br />

	<div class="buttonA">
		<a class="buttonA" href="<?php echo Globals::getSendGlobalMessageHREF(); ?>">Send Global Message</a>
	</div>
	<br /><br />

	<?php
	if (!empty($AllRows)) { ?>
		<table id="cpl" class="center standard inset">
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
						<td class="sort_name left" data-name="<?php echo $Row['player']->getPlayerName(); ?>" valign="top"><?php echo $Row['name_link']; ?></td>
						<td class="sort_race">
							<?php echo $ThisPlayer->getColouredRaceName($Row['player']->getRaceID(), true); ?>
						</td>
						<td class="sort_alliance"><?php echo $Row['player']->getAllianceDisplayName(true); ?></td>
						<td class="sort_exp right"><?php echo number_format($Row['player']->getExperience()); ?></td>
					</tr><?php
				} ?>
			</tbody>
		</table>

		<?php $this->setListjsInclude('current_players');
	} ?>
</div>
