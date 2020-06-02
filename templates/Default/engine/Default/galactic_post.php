Welcome <?php echo $ThisPlayer->getDisplayName(); ?>, your position is <i>Editor</i><br /><br />
<b>EDITOR OPTIONS</b>
<ul>
	<li><a href="<?php echo $ViewArticlesHREF; ?>">View the articles</a></li>
	<li><a href="<?php echo $MakePaperHREF; ?>">Make a paper</a></li>
</ul>
<br /><?php
if (!empty($Papers)) { ?>
	The following papers are already made (papers must have 3-8 articles to go to the press):
	<br /><br /><?php
}
foreach ($Papers as $Paper) { ?>
	<span class="red">***</span><i><?php echo $Paper['title']; ?></i>
	 which contains <span class="<?php echo $Paper['color']; ?>"><?php echo $Paper['num_articles']; ?> </span>articles.<?php
	if ($Paper['published']) { ?>
		<span class="bold green">PUBLISHED!</span><?php
	} elseif (isset($Paper['PublishHREF'])) { ?>
		<a href="<?php echo $Paper['PublishHREF']; ?>"><b>HIT THE PRESS!</b></a><?php
	} ?>

	<br />
	<a href="<?php echo $Paper['DeleteHREF']; ?>">Delete <?php echo $Paper['title']; ?></a>
	<br />
	<a href="<?php echo $Paper['EditHREF']; ?>">Edit <?php echo $Paper['title']; ?></a>
	<br /><br /><?php
} ?>
<p>Note: If you wish to edit an article you must first view it.</p>
