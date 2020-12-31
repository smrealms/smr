<div class="center">
	So you want to name your ship?  Great!

	So...what do you want to name it? (max 48 text chars)<br /><br />
	<form name="ship_naming" method="POST" action="<?php echo $ShipNameFormHref; ?>">
		<input type="text" name="ship_name" required placeholder="Enter Name Here">
		<br /><br />
		<button type="submit" name="action" value="text">Get It Painted! (<?php echo $Costs['text']; ?> SMR Credits)</button>
		<br /><br />
		<button type="submit" name="action" value="html">Include HTML (<?php echo $Costs['html']; ?> SMR Credits)</button>
	</form>
	<br /><br /><br />
	Or you can paint a logo on your ship! (max <?php echo MAX_IMAGE_HEIGHT; ?> height by <?php echo MAX_IMAGE_WIDTH; ?> width and <?php echo MAX_IMAGE_SIZE; ?>kB)<br /><br />
	<form name="ship_logo" enctype="multipart/form-data" method="POST" action="<?php echo $ShipNameFormHref; ?>">
		Image: <input type="file" name="photo" required accept="image/jpeg, image/png" style="width:40%;">
		<br /><br />
		<button type="submit" name="action" value="logo">Paint A Logo (<?php echo $Costs['logo']; ?> SMR Credits)</button>
	</form>
</div>
