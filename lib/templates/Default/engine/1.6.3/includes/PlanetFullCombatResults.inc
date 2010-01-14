<?php
if(!$AlreadyDestroyed)
{
	?><h1>Attacker Results</h1><br />
	<?php $this->includeTemplate('includes/PlanetTraderTeamCombatResults.inc',array('TraderTeamCombatResults'=>$FullPlanetCombatResults['Attackers'])); ?>
	<br />
	<br /><?php
}
else
{
	?><span style="font-weight:bold;">The planet is already destroyed.</span><br /><br /><?php
} ?>
<img src="images/planetAttack.jpg" width="480px" height="330px" alt="Planet Attack" title="Planet Attack"><br /><?php
if(!$AlreadyDestroyed)
{ ?>
	<br />
	<h1>Planet Results</h1><br />
	<?php $this->includeTemplate('includes/PlanetCombatResults.inc',array('PlanetCombatResults'=>$FullPlanetCombatResults['Planet']));
} ?>