<div align="center"><?php
	if(isset($Preview))
	{ ?>
		<div align="center">If you ship is found to use HTML inappropriately you may be banned.
		  Inappropriate HTML includes but is not limited to something that can either cause display errors or cause functionality of the game to stop.  Also it is your responsibility to make sure ALL HTML tags that need to be closed are closed!<br />
			Preview:<br />
			<?php echo $Preview; ?><br />
		</div>
		Are you sure you want to continue?<br />
		<a href="<?php echo $ContinueHref; ?>" class="submitStyle">Continue </a> <a href="<?php echo Globals::getBuyShipNameHref(); ?>" class="submitStyle">Back</a><?php
	}
	else
	{ ?>
		So you want to name your ship?  Great!
					
		So...what do you want to name it? (max 48 text chars) (max 30 height by 200 width and 20k for logos)<br />
		<form name="ship_naming" enctype="multipart/form-data" method="POST" action="<?php echo $ShipNameFormHref; ?>">
			<input type="text" name="ship_name" value="Enter Name Here" id="InputFields"><br />
			<br />
			<input type="submit" name="action" value="Get It Painted! (1 SMR Credit)" id="InputFields" /><br />
			<br />
			<input type="submit" name="action" value="Include HTML (2 SMR Credits)" id="InputFields" /><br />
			<br />
			Image: <input type="file" name="photo" accept="image/jpeg" id="InputFields" style="width:40%;"><br />
			<br />
			<input type="submit" name="action" value="Paint a logo (3 SMR Credits)" id="InputFields" />
		</form><?php
	} ?>
</div>