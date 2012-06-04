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
<br />
<h2>Plot To Nearest</h2><br />
<form class="standard" id="SelectXTypeForm" method="POST" action="">
	<select name="xtype"><?php
	foreach($AllXTypes as $EachXType)
	{
		?><option value="<?php echo $EachXType; ?>"<?php if(isset($XType)&&$EachXType==$XType){ ?> selected="selected"<?php } ?>><?php echo $EachXType; ?></option><?php
	} ?>
	</select>&nbsp;
	<input type="submit" value="Select" />
</form><?php
if(isset($XType))
{ ?>
	<form class="standard" id="PlotNearestForm" method="POST" action="<?php echo $PlotNearestFormLink; ?>">
		<input type="hidden" name="xtype" value="<?php echo $XType; ?>" /><br /><br />
		<select name="X"><?php
			switch($XType)
			{
				case 'Technology':
					$Hardwares =& Globals::getHardwareTypes();
					foreach($Hardwares as &$Hardware)
					{
						?><option value="<?php echo $Hardware['ID']; ?>"><?php echo $Hardware['Name']; ?></option><?php
					} unset($Hardware);
				break;
				case 'Ships':
					$Ships =& AbstractSmrShip::getAllBaseShips(Globals::getGameType(SmrSession::$game_id));
					foreach($Ships as &$Ship)
					{
						?><option value="<?php echo $Ship['ShipTypeID']; ?>"><?php echo $Ship['Name']; ?></option><?php
					} unset($Ship);
				break;
				case 'Weapons':
					$Weapons =& SmrWeapon::getAllWeapons(Globals::getGameType(SmrSession::$game_id));
					foreach($Weapons as &$Weapon)
					{
						?><option value="<?php echo $Weapon->getWeaponTypeID(); ?>"><?php echo $Weapon->getName(); ?></option><?php
					} unset($Weapon);
				break;
				case 'Locations':
					$Locations =& SmrLocation::getAllLocations();
					foreach($Locations as &$Location)
					{
						?><option value="<?php echo $Location->getTypeID(); ?>"><?php echo $Location->getName(); ?></option><?php
					} unset($Location);
				break;
				case 'Goods': 
					$Goods =& Globals::getGoods();
					foreach($Goods as &$Good)
					{
						?><option value="<?php echo $Good['ID']; ?>"><?php echo $Good['Name']; ?></option><?php
					} unset($Good);
				break;
				default:
			} ?>
		</select>&nbsp;
		<input type="submit" value="Go" />
	</form><?php
} ?>
</form>