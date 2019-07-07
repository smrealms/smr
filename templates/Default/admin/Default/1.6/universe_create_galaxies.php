<form method="POST" action="<?php echo $UpdateNumGalsHREF; ?>">
	Number of Galaxies:
	<input class="center" type="number" min="1" max="30" name="num_gals" value="<?php echo $NumGals; ?>" />
	<input type="submit" name="submit" value="Update" />
</form>
<br />
<?php $this->includeTemplate('1.6/GalaxyDetails.inc'); ?>

<br /><br />
<form method="POST" enctype="multipart/form-data" action="<?php echo $UploadSmrFileHREF; ?>">
	Or generate the universe from a SMR file:<br />
	<input type="file" name="smr_file" />&nbsp;
	<input type="submit" value="Upload SMR File" name="submit" />
</form>
