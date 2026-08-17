<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Path;

class PlotCourseResultRenderer {

public static function render(Path $Path, string $FullPath): void {
?>
<p>The plotted course is <?php echo pluralise($Path->getLength(), 'sector'); ?> long and costs <?php echo pluralise($Path->getTurns(), 'turn'); ?> to traverse.</p>

<br />
<h2>Plotted Course</h2>
<p><?php echo $FullPath; ?></p>
<?php
}

}
