<?php declare(strict_types=1);

namespace Smr\Pages\Player\GalacticPost;

class ArticleDeleteConfirmRenderer {

	public static function render(string $ConfirmHREF, string $CancelHREF, string $ArticleTitle): void {
		?>
		Are you sure you want to delete the article titled <b><?php echo $ArticleTitle; ?></b>?
		<br /><br />
		<?php echo create_submit_link($ConfirmHREF, 'Yes'); ?>&nbsp;
		<?php echo create_submit_link($CancelHREF, 'No');
	}

}
