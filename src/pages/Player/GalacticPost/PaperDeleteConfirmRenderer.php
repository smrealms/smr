<?php declare(strict_types=1);

namespace Smr\Pages\Player\GalacticPost;

class PaperDeleteConfirmRenderer {

/**
 * @param array<string> $Articles
 */
public static function render(string $PaperTitle, array $Articles, string $ConfirmHREF, string $CancelHREF): void {
?>
Are you sure you want to delete the paper titled <b><?php echo $PaperTitle; ?></b>?<?php
if (count($Articles) > 0) { ?>
	This paper contains the following articles:
	<ul><?php
		foreach ($Articles as $Article) { ?>
			<li><?php echo $Article; ?></li><?php
		} ?>
	</ul><?php
} else { ?>
	This paper contains no articles.<br /><br /><?php
} ?>
<form method="POST" action="<?php echo $ConfirmHREF; ?>"><?php
	if (count($Articles) > 0) { ?>
		Do you want to also delete the articles in this paper?<br />
		<input type="radio" name="delete_articles" value="Yes" />Yes<br />
		<input type="radio" name="delete_articles" value="No" />No<br /><br /><?php
	} else { ?>
		<input type="hidden" name="delete_articles" value="No" /><?php
	} ?>
	<?php echo create_submit_display('Yes'); ?>&nbsp;
	<?php echo create_submit_link($CancelHREF, 'No') ?>
</form>

<?php
}

}
