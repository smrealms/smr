<?php
if(!$AlreadyDestroyed) {
	if(!$MinimalDisplay) { ?>
		<h1>Attacker Results</h1><br /><?php
	}
	$this->includeTemplate('includes/PortTraderTeamCombatResults.inc',array('TraderTeamCombatResults'=>$FullPortCombatResults['Attackers'],'MinimalDisplay'=>$MinimalDisplay));
}
else if(!$MinimalDisplay) {
	?><span class="bold">The port is already destroyed.</span><?php
}
?><br /><?php
if(!$MinimalDisplay) { ?>
	<br />
	<img src="images/portAttack.jpg" width="480" height="330" alt="Port Attack" title="Port Attack"><br /><?php
}
if(!$AlreadyDestroyed) {
	if(!$MinimalDisplay) { ?>
		<br />
		<h1>Port Results</h1><br /><?php
	}
	$this->includeTemplate('includes/PortCombatResults.inc',array('PortCombatResults'=>$FullPortCombatResults['Port'],'MinimalDisplay'=>$MinimalDisplay));
} ?>
