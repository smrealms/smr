<?php
if (!empty($BackHREF)) { ?>
	<a href="<?php echo $BackHREF; ?>"><b>&lt;&lt;Back</b></a><?php
}

if (empty($ArticleLayout)) { ?>
	There is no current edition of the Galactic Post for this game.<?php
} else { ?>
	<table class="center" spacepadding="20" cellspacing="20"><?php
		foreach ($ArticleLayout as $Columns) { ?>
			<tr><?php
				foreach ($Columns as $Column) { ?>
					<td class="center top" width=50%>
						<font size="6"><?php echo bbifyMessage($Column['title']); ?></font><br /><br />
						<div style="text-align: justify;"><?php echo bbifyMessage($Column['text']); ?></div><br /><br />
					</td><?php
				} ?>
			</tr><?php
		} ?>
	</table><?php
} ?>
