<?php declare(strict_types=1);

namespace Smr\Pages\Player\GalacticPost;

class PaperMakeRenderer {

public static function render(?string $SubmitHREF): void {
?>
What is the title of this edition?<br />
<form method="POST" action="<?php echo $SubmitHREF; ?>">
	<input type="text" name="title" required class="center" style="width:525;"><br /><br />
	<?php echo create_submit_display('Make the paper'); ?>
</form>
<?php }

}
