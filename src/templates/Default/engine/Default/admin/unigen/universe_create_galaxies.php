<?php declare(strict_types=1);

/**
 * @var string $UpdateNumGalsHREF
 * @var string $UploadSmrFileHREF
 * @var string $GenerateHREF
 */

?>
<form method="POST" action="<?php echo $UpdateNumGalsHREF; ?>">
	Number of Galaxies:
	<input class="center" type="number" min="1" max="30" name="num_gals" value="<?php echo $NumGals; ?>" />
	<?php echo create_submit('submit', 'Update'); ?>
</form>
<br />
<?php $this->includeTemplate('admin/unigen/GalaxyDetails.inc.php'); ?>

<br /><br />
<form method="POST" enctype="multipart/form-data" action="<?php echo $UploadSmrFileHREF; ?>">
	Or generate the universe from a SMR file:<br />
	<input type="file" name="smr_file" />&nbsp;
	<?php echo create_submit('submit', 'Upload SMR File'); ?>
</form>

<br /><br />
Or automatically generate a pre-populated map: <a href="<?php echo $GenerateHREF; ?>" class="submitStyle">Generate</a>
<br />
<small><span class="bold">WARNING: </span> This is slow!</small>
