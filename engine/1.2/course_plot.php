<?php

print_topic("PLOT A COURSE");

// create menu
$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'course_plot.php';
$menue_items[] = create_link($container, 'Plot a Course');
if($player->land_on_planet == 'FALSE') {
	$container['body'] = 'map_local.php';
	$menue_items[] = create_link($container, 'Local Map');
}
$menue_items[] = '<a href="' . URL . '/map_galaxy.php" target="_blank">Galaxy Map</a>';

// print it
print_menue($menue_items);

$container=array();
$container['url'] = 'course_plot_processing.php';
$container['body'] = '';
$form = create_form($container,'Plot Course');

echo $form['form'];
echo '
<h2>Conventional</h2><br>
<table cellspacing="0" cellpadding="0" class="nobord nohpad">
	<tr>
		<td>From:&nbsp;</td>
		<td><input type="text" size="5" name="from" maxlength="5" class="center" value="';

echo $player->sector_id;

echo '"></td>
		<td>&nbsp;&nbsp;&nbsp;&nbsp;To:&nbsp;</td>
		<td><input type="text" size="5" name="to" maxlength="5" class="center"></td>
		<td>&nbsp;&nbsp;&nbsp;&nbsp;';

echo $form['submit'];

echo '	</td>
	</tr>
</table>
</form>';

if (!empty($ship->hardware[HARDWARE_JUMP])) {
	$container=array();
	$container["url"] = "sector_jump_processing.php";
	$container["target_page"] = "current_sector.php";
	$form = create_form($container,'Engage Jump (15)');

	echo $form['form'];
	echo '
	<br>
	<h2>Jumpdrive</h2><br>
	<table cellspacing="0" cellpadding="0" class="nobord nohpad">
		<tr>

			<td>Jump To:&nbsp;</td>
			<td><input type="text" size="5" name="to" maxlength="5" class="center"></td>
			<td>&nbsp;&nbsp;&nbsp;&nbsp;';

	echo $form['submit'];
	
	echo '	</td>
		</tr>
	</table>
	</form>';
}
?>
