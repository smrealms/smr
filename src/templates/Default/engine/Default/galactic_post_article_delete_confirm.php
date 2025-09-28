<?php declare(strict_types=1);

/**
 * @var string $ConfirmHREF
 * @var string $CancelHREF
 * @var string $ArticleTitle
 */

?>
Are you sure you want to delete the article titled <b><?php echo $ArticleTitle; ?></b>?
<br /><br />
<?php echo create_submit_link($ConfirmHREF, 'Yes'); ?>&nbsp;
<?php echo create_submit_link($CancelHREF, 'No');
