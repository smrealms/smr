<span class="red">WARNING WARNING</span> port assault about to commence!!<br />
Are you sure you want to attack this port?<br /><br />
<?php
if ($ThisShip->hasScanner()) {
	$Port = $ThisSector->getPort(); ?>
	Your scanners detect that there <?php if($Port->getShields()==1){ ?>is<?php }else{ ?>are<?php } ?> <span id="portShields"><?php echo $Port->getShields(); ?></span> shield<?php if($Port->getShields()!=1){ ?>s<?php } ?>,
	and <span id="portCDs"><?php echo $Port->getCDs(); ?></span> combat drone<?php if($Port->getCDs()!=1){ ?>s<?php } ?>,
	and <span id="portArmour"><?php echo $Port->getArmour(); ?></span> plate<?php if($Port->getArmour()!=1){ ?>s<?php } ?> of armour.<br /><br /><?php
} ?>

<div class="buttonA">
	<a class="buttonA" href="<?php echo $PortAttackHREF; ?>">&nbsp;Yes&nbsp;</a>
</div>&nbsp;
<div class="buttonA">
	<a class="buttonA" href="<?php echo Globals::getCurrentSectorHREF(); ?>">&nbsp;No&nbsp;</a>
</div>