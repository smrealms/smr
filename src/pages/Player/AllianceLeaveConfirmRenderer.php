<?php declare(strict_types=1);

namespace Smr\Pages\Player;

class AllianceLeaveConfirmRenderer {

public static function render(string $YesHREF, string $NoHREF): void {
?>
Do you really want to leave this alliance?<br /><br />

<div class="buttonA">
	<a class="buttonA" href="<?php echo $YesHREF; ?>">Yes</a>
	&nbsp;&nbsp;&nbsp;
	<a class="buttonA" href="<?php echo $NoHREF; ?>">No</a>
</div>
<?php }

}
