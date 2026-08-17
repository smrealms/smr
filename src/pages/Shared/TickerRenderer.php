<?php declare(strict_types=1);

namespace Smr\Pages\Shared;

class TickerRenderer {

/**
 * @param ?array<array{Time: string, Message: string}> $Ticker
 */
public static function render(?array $Ticker): void {
if (isset($Ticker)) { ?>
	<div id="ticker" class="ajax left" style="overflow:auto;height:8em;border:2px solid #0b8d45;"><?php
		if (count($Ticker) > 0) {
			foreach ($Ticker as $Tick) {
				echo $Tick['Time']; ?>: &nbsp; <?php echo bbify($Tick['Message']); ?><br /><br /><?php
			}
		} else {
			?>Nothing to report<?php
		} ?>
	</div><br /><?php
}

}

}
