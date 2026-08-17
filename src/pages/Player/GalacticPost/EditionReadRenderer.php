<?php declare(strict_types=1);

/**
 * @var ?int $PaperGameID
 * @var ?string $BackHREF
 * @var ?array<int, array<int, array{title: string, text: string}>> $ArticleLayout
 */

if (isset($BackHREF)) { ?>
	<a href="<?php echo $BackHREF; ?>"><b>&lt;&lt;Back</b></a><?php
}

if (!isset($ArticleLayout)) { ?>
	There is no current edition of the Galactic Post for this game.<?php
} else { ?>
	<table class="center" spacepadding="20" cellspacing="20"><?php
		foreach ($ArticleLayout as $Columns) { ?>
			<tr><?php
				foreach ($Columns as $Column) { ?>
					<td class="center top" width=50%>
						<font size="6"><?php echo bbify($Column['title'], $PaperGameID); ?></font><br /><br />
						<div style="text-align: justify;"><?php echo bbify($Column['text'], $PaperGameID); ?></div><br /><br />
					</td><?php
				} ?>
			</tr><?php
		} ?>
	</table><?php
}
