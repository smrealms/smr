<?php declare(strict_types=1);

if (!$MinimalDisplay) { ?>
	<h1>Attacker Results</h1><br /><?php
}
$this->includeTemplate('includes/PlanetTraderTeamCombatResults.inc.php', ['TraderTeamCombatResults' => $FullPlanetCombatResults['Attackers'], 'MinimalDisplay' => $MinimalDisplay]);
?><br /><?php
if (!$MinimalDisplay) { ?>
	<br />
	<img src="images/planetAttack.jpg" width="480" height="330" alt="Planet Attack" title="Planet Attack"><br />
	<br />
	<h1>Planet Results</h1><br /><?php
}
$this->includeTemplate('includes/PlanetCombatResults.inc.php', ['PlanetCombatResults' => $FullPlanetCombatResults['Planet'], 'MinimalDisplay' => $MinimalDisplay]);
?>
