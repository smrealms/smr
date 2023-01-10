<?php declare(strict_types=1);

if ($MinimalDisplay) { ?>
	<h2>Attacker Results</h2><?php
} else { ?>
	<h1>Attacker Results</h1><?php
} ?>
<br /><?php

$this->includeTemplate('includes/TraderTeamCombatResults.inc.php', [
	'TraderTeamCombatResults' => $TraderCombatResults['Attackers'],
	'MinimalDisplay' => $MinimalDisplay,
]); ?>

<br /><br /><?php
if ($MinimalDisplay) { ?>
	<h2>Defender Results</h2><?php
} else { ?>
	<img src="images/creonti_cruiser.jpg" alt="Creonti Cruiser" title="Creonti Cruiser"><br />
	<br />
	<h1>Defender Results</h1><?php
} ?>
<br /><?php

$this->includeTemplate('includes/TraderTeamCombatResults.inc.php', [
	'TraderTeamCombatResults' => $TraderCombatResults['Defenders'],
	'MinimalDisplay' => $MinimalDisplay,
]);

if ($MinimalDisplay) {
	echo $AttackLogLink;
}
