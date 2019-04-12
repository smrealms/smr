<span class="bold"><?php echo $PaperTitle; ?></span>
<br /><br /><?php
if (empty($Articles)) { ?>
	This paper has no articles yet!<?php
} else { ?>
	<ul><?php
		foreach ($Articles as $Article) { ?>
			<li>
				<h2><?php echo $Article['title']; ?></h2><br />
				<?php echo $Article['text']; ?><br /><br />
				<a href="<?php echo $Article['editHREF']; ?>">[x] Remove this article from <?php echo $PaperTitle; ?></a>
			</li>
			<br /><br /><?php
		} ?>
	</ul><?php
} ?>
