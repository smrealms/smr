<?php declare(strict_types=1);

namespace Smr\Pages\Album;

class SearchResultsRenderer {

/**
 * @param array<string> $Nicks
 */
public static function render(array $Nicks): void {
?>
<div class="center big">Please make a selection!</div>

<ul style="columns: 4;"><?php
	foreach ($Nicks as $Nick) { ?>
		<li><a href="?nick=<?php echo urlencode($Nick); ?>"><?php echo htmlentities($Nick); ?></a></li><?php
	} ?>
</ul>
<?php
}

}
