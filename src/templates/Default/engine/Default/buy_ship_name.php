<?php declare(strict_types=1);

/**
 * @var Smr\Pages\Player\BuyShipNameProcessor $ProcessorPage
 * @var array{text: int, html: int, logo: int} $Costs
 */

?>
<div class="center">
	So you want to name your ship?  Great!

	So...what do you want to name it? (max 48 text chars)<br /><br />
	<form name="ship_naming" method="POST" action="<?php echo $ProcessorPage->href(); ?>">
		<input type="text" name="ship_name" required placeholder="Enter Name Here">
		<br /><br />
		<?php echo $ProcessorPage->actionText->html('Get It Painted! (' . $Costs['text'] . ' SMR Credits)'); ?>
		<br /><br />
		<?php echo $ProcessorPage->actionHtml->html('Include HTML (' . $Costs['html'] . ' SMR Credits)'); ?>
	</form>
	<br /><br /><br />
	Or you can paint a logo on your ship! (max <?php echo MAX_IMAGE_HEIGHT; ?> height by <?php echo MAX_IMAGE_WIDTH; ?> width and <?php echo MAX_IMAGE_SIZE; ?>kB)<br /><br />
	<form name="ship_logo" enctype="multipart/form-data" method="POST" action="<?php echo $ProcessorPage->href(); ?>">
		Image: <input type="file" name="photo" required accept="image/jpeg, image/png" style="width:40%;">
		<br /><br />
		<?php echo $ProcessorPage->actionLogo->html('Paint A Logo (' . $Costs['logo'] . ' SMR Credits)'); ?>
	</form>
</div>
