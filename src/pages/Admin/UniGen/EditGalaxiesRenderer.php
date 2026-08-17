<?php declare(strict_types=1);

/**
 * @var string $AddHREF
 * @var string $BackHREF
 * @var bool $GameEnabled
 * @var int $MaxAddId
 */

?>

<?php $this->includeTemplate('admin/unigen/GalaxyDetails.inc.php'); ?>

<?php
if ($GameEnabled) { ?>
	<p>
		<span class="bold">NOTE: </span>Galaxy sizes cannot be changed because
		this game has already been enabled.
	</p><?php
} else { ?>
	<p>
		<span class="red bold">WARNING: </span>If you modify galaxy sizes,
		any ports, planets, and locations in sectors that are removed will also
		be removed! Relative locations may also shift.
	</p>
	<br />

	<form method=POST action="<?php echo $AddHREF; ?>">
		Add new galaxy ID: <input required name="insert_galaxy_id" type="number" min="1" max="<?php echo $MaxAddId; ?>" /> <?php echo create_submit_display('Insert'); ?>
	</form><?php
} ?>

<br />
<a href="<?php echo $BackHREF; ?>">&lt;&lt; Back</a>
