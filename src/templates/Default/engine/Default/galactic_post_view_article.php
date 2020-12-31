<?php
if (empty($Articles)) { ?>
	<p>All articles have been assigned to a paper.</p><?php
} else { ?>
	It is your responsibility to make sure ALL HTML tags are closed!<br />
	You have the following articles to view.<br /><br /><?php
	foreach ($Articles as $Article) { ?>
		<a href="<?php echo $Article['link']; ?>">
			<span class="yellow"><?php echo $Article['title']; ?></span> written by <?php echo $Article['writer']; ?>
		</a>
		<br /><?php
	}
} ?>

<br /><br /><?php
if (isset($SelectedArticle)) { ?>
	<h2><?php echo $SelectedArticle['title']; ?></h2>
	<p><?php echo $SelectedArticle['text']; ?></p>
	<a href="<?php echo $SelectedArticle['editHREF']; ?>"><b>Edit this article</b></a>
	<br />
	<a href="<?php echo $SelectedArticle['deleteHREF']; ?>"><b>Delete This article</b></a>
	<br /><br /><?php
	if (empty($Papers)) { ?>
		You have no papers made that you can add an article to.
		<a href="<?php echo $MakePaperHREF; ?>"><b>Click Here</b></a> to make a new one.<br /><?php
	} else {
		foreach ($Papers as $Paper) { ?>
			<a href="<?php echo $Paper['addHREF']; ?>">
				<b>Add this article to <?php echo $Paper['title']; ?>!</b>
			</a>
			<br /><?php
		}
	} ?>

	<br /><?php
	if ($AddedToNews) { ?>
		<span class="green">SUCCESS</span>: added article to Breaking News<?php
	} else { ?>
		<a href="<?php echo $AddToNewsHREF; ?>">
			<b>Add this article to Breaking News</b>
		</a>
		<br /><small>note: breaking news is in the news section.</small><?php
	}
}
