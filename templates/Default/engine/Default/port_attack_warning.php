<span class="red">WARNING WARNING</span> port assault about to commence!!<br />
Are you sure you want to attack this port?<br /><br />
<?php
if ($ThisShip->hasScanner()) {
	$Port = $ThisSector->getPort(); ?>
	Your scanners detect that there <?php echo $this->pluralise('is', $Port->getShields()); ?> <span id="portShields"><?php echo $Port->getShields(); ?></span> <?php echo $this->pluralise('shield', $Port->getShields()); ?>,
	and <span id="portCDs"><?php echo $Port->getCDs(); ?></span> <?php echo $this->pluralise('combat drone', $Port->getCDs()); ?>,
	and <span id="portArmour"><?php echo $Port->getArmour(); ?></span> <?php echo $this->pluralise('plate', $Port->getArmour()); ?> of armour.<br /><br /><?php
} ?>

<div class="buttonA">
	<a class="buttonA" href="<?php echo $PortAttackHREF; ?>">&nbsp;Yes&nbsp;</a>
</div>&nbsp;
<div class="buttonA">
	<a class="buttonA" href="<?php echo Globals::getCurrentSectorHREF(); ?>">&nbsp;No&nbsp;</a>
</div>