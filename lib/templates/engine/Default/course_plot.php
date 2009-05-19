<?php
$MenuItems = array(array('Link'=>$PlotCourseLink,'Text'=>'Plot a Course'));
if(!$ThisPlayer->isLandedOnPlanet())
	$MenuItems[] = array('Link'=>$LocalMapLink,'Text'=>'Local Map');
$MenuItems[] = array('Link'=>'map_galaxy.php" target="_blank','Text'=>'Galaxy Map');
$this->includeTemplate('includes/menu.inc',array('MenuItems' => $MenuItems)); ?>


<form class="standard" id="PlotCourseForm" method="POST" action="<?php echo $PlotCourseFormLink; ?>">
	<h2>Conventional</h2><br />
	<table cellspacing="0" cellpadding="0" class="nobord nohpad">
		<tr>
			<td>From:&nbsp;</td>
			<td><input type="text" size="5" name="from" maxlength="5" class="center" value="<?php echo $ThisPlayer->getSectorID(); ?>"></td>
			<td>&nbsp;&nbsp;&nbsp;&nbsp;To:&nbsp;</td>
			<td><input type="text" size="5" name="to" maxlength="5" class="center"></td>
			<td>&nbsp;&nbsp;&nbsp;&nbsp;<input class="submit" type="submit" name="action" value="Plot Course"></td>
		</tr>
	</table>
</form><?php
if ($ThisShip->hasJump())
{ ?>
	<br />
	<form class="standard" id="JumpDriveForm" method="POST" action="<?php echo $JumpDriveFormLink; ?>">
		<h2>Jumpdrive</h2><br />
		<table cellspacing="0" cellpadding="0" class="nobord nohpad">
			<tr>
				<td>Jump To:&nbsp;</td>
				<td><input type="text" size="5" name="target" maxlength="5" class="center"></td>
				<td>&nbsp;&nbsp;&nbsp;&nbsp;<input class="submit" type="submit" name="action" value="Engage Jump (15)"></td>
			</tr>
		</table>
	</form><?php
} ?>