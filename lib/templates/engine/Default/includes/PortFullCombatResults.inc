<?php
if(!$AlreadyDestroyed)
{ ?>
	<h1>Attacker Results</h1><br />
	<?php $this->includeTemplate('includes/PortTraderTeamCombatResults.inc',array('TraderTeamCombatResults'=>$FullPortCombatResults['Attackers'],'MinimalDisplay'=>$MinimalDisplay));
}
else
{
	?><span style="font-weight:bold;">The port is already destroyed.</span><?php
}

if(!$MinimalDisplay)
{ ?>
	<br />
	<br />
	<img src="images/portAttack.jpg" width="480px" height="330px" alt="Port Attack" title="Port Attack"><br /><?php
}
if(!$AlreadyDestroyed)
{ ?>
	<br />
	<h1>Port Results</h1><br />
	<?php $this->includeTemplate('includes/PortCombatResults.inc',array('PortCombatResults'=>$FullPortCombatResults['Port']));
} ?>