<div class="center">
	So you want to name your ship?  Great!

	So...what do you want to name it? (max 48 text chars)<br /><br />
	<form name="ship_naming" method="POST" action="<?php echo $ShipNameFormHref; ?>">
		<input type="text" name="ship_name" required placeholder="Enter Name Here" class="InputFields">
		<br /><br />
		<input type="submit" name="action" value="Get It Painted! (<?php echo CREDITS_PER_TEXT_SHIP_NAME; ?> SMR Credit)" class="InputFields" />
		<br /><br />
		<input type="submit" name="action" value="Include HTML (<?php echo CREDITS_PER_HTML_SHIP_NAME; ?> SMR Credits)" class="InputFields" />
	</form>
	<br /><br /><br />
	Or you can paint a logo on your ship! (max <?php echo MAX_IMAGE_HEIGHT; ?> height by <?php echo MAX_IMAGE_WIDTH; ?> width and <?php echo MAX_IMAGE_SIZE; ?>kB)<br /><br />
	<form name="ship_logo" enctype="multipart/form-data" method="POST" action="<?php echo $ShipNameFormHref; ?>">
		Image: <input type="file" name="photo" required accept="image/jpeg, image/png" class="InputFields" style="width:40%;">
		<br /><br />
		<input type="submit" name="action" value="Paint a logo (<?php echo CREDITS_PER_SHIP_LOGO; ?> SMR Credits)" class="InputFields" />
	</form>
</div>
