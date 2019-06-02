<div class="center"><?php
	if (isset($Preview)) { ?>
		<div class="center">If you ship is found to use HTML inappropriately you may be banned.
		  Inappropriate HTML includes but is not limited to something that can either cause display errors or cause functionality of the game to stop.  Also it is your responsibility to make sure ALL HTML tags that need to be closed are closed!<br />
			Preview:<br />
			<?php echo $Preview; ?>
		</div><br />
		Are you sure you want to continue?<br />
		<br />
		<a href="<?php echo $ContinueHref; ?>" class="submitStyle">Continue </a> <a href="<?php echo Globals::getBuyShipNameHref(); ?>" class="submitStyle">Back</a><?php
	}
	else { ?>
		So you want to name your ship?  Great!

		So...what do you want to name it? (max 48 text chars) (max <?php echo MAX_IMAGE_HEIGHT; ?> height by <?php echo MAX_IMAGE_WIDTH; ?> width and <?php echo MAX_IMAGE_SIZE; ?>k for logos)<br />
		<form name="ship_naming" enctype="multipart/form-data" method="POST" action="<?php echo $ShipNameFormHref; ?>">
			<input type="text" name="ship_name" value="Enter Name Here" class="InputFields"><br />
			<br />
			<input type="submit" name="action" value="Get It Painted! (<?php echo CREDITS_PER_TEXT_SHIP_NAME; ?> SMR Credit)" class="InputFields" /><br />
			<br />
			<input type="submit" name="action" value="Include HTML (<?php echo CREDITS_PER_HTML_SHIP_NAME; ?> SMR Credits)" class="InputFields" /><br />
			<br />
			Image: <input type="file" name="photo" accept="image/jpeg, image/png" class="InputFields" style="width:40%;"><br />
			<br />
			<input type="submit" name="action" value="Paint a logo (<?php echo CREDITS_PER_SHIP_LOGO; ?> SMR Credits)" class="InputFields" />
		</form><?php
	} ?>
</div>
