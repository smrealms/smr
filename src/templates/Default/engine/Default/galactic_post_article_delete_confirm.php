<?php declare(strict_types=1);

/**
 * @var string $SubmitHREF
 * @var string $ArticleTitle
 */

?>
Are you sure you want to delete the article titled <b><?php echo $ArticleTitle; ?></b>?
<br /><br />
<form method="POST" action="<?php echo $SubmitHREF; ?>">
	<?php echo create_submit('action', 'Yes'); ?>&nbsp;
	<?php echo create_submit('action', 'No'); ?>
</form>
