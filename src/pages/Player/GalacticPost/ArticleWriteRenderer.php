<?php declare(strict_types=1);

namespace Smr\Pages\Player\GalacticPost;

class ArticleWriteRenderer {

	public static function render(?string $PreviewTitle, ?string $Preview, ArticleWriteProcessor $SubmitArticlePage): void {
		if ($PreviewTitle !== null && $Preview !== null) { ?>
			<table class="standard">
				<tr>
					<td>Title:</td>
					<td><?php echo bbify($PreviewTitle); ?></td>
				</tr>
				<tr>
					<td>Article:</td>
					<td><?php echo bbify($Preview); ?></td>
				</tr>
			</table><br /><br /><?php
		} ?>
		What is the title?<br />
		<form name="GPArticleForm" method="POST" action="<?php echo $SubmitArticlePage->href(); ?>">
			<input type="text" name="title" class="center" style="width:525;" value="<?php if ($PreviewTitle !== null) { echo htmlspecialchars($PreviewTitle); } ?>" required><br /><br />
			<br />Write what you want to write here!<br />
			<textarea spellcheck="true" name="message" required><?php if ($Preview !== null) { echo $Preview; } ?></textarea><br /><br />
			<?php echo $SubmitArticlePage->actionSubmit->html(); ?>&nbsp;<?php echo $SubmitArticlePage->actionPreview->html(); ?>
		</form>

		<?php
	}

}
