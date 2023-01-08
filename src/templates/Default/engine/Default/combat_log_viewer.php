<?php declare(strict_types=1);

/**
 * @var Smr\Template $this
 * @var int $CombatLogSector
 * @var string $CombatLogTimestamp
 * @var string $CombatResultsType
 * @var array<mixed> $CombatResults
 */

if (isset($PreviousLogHREF) || isset($NextLogHREF)) { ?>
	<div class="center"><?php
	if (isset($PreviousLogHREF)) {
		?><a href="<?php echo $PreviousLogHREF ?>"><img title="Previous" alt="Previous" src="images/album/rew.jpg" /></a><?php
	}
	if (isset($NextLogHREF)) {
		?><a href="<?php echo $NextLogHREF ?>"><img title="Next" alt="Next" src="images/album/fwd.jpg" /></a><?php
	} ?>
	</div><?php
} ?>
Sector <?php echo $CombatLogSector ?><br />
<?php echo $CombatLogTimestamp ?><br />
<br />

<?php
if ($CombatResultsType == 'PLAYER') {
	$this->includeTemplate('includes/TraderFullCombatResults.inc.php', [
		'TraderCombatResults' => $CombatResults,
		'MinimalDisplay' => false,
	]);
} elseif ($CombatResultsType == 'FORCE') {
	$this->includeTemplate('includes/ForceFullCombatResults.inc.php', ['FullForceCombatResults' => $CombatResults]);
} elseif ($CombatResultsType == 'PORT') {
	$this->includeTemplate('includes/PortFullCombatResults.inc.php', [
		'FullPortCombatResults' => $CombatResults,
		'MinimalDisplay' => false,
		'AlreadyDestroyed' => false,
	]);
} elseif ($CombatResultsType == 'PLANET') {
	$this->includeTemplate('includes/PlanetFullCombatResults.inc.php', [
		'FullPlanetCombatResults' => $CombatResults,
		'MinimalDisplay' => false,
	]);
}
