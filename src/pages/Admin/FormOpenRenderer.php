<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

class FormOpenRenderer {

public static function render(string $Color, string $Status, string $ToggleHREF, string $Action): void {
?>
<p>Feature Request Status: <span class="<?php echo $Color; ?>"><?php echo $Status; ?></span></p>
<a class="submitStyle" href="<?php echo $ToggleHREF; ?>"><?php echo $Action; ?> Form</a>
<?php
}

}
