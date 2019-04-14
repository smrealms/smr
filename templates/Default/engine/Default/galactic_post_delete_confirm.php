<?php
if (isset($ArticleTitle)) { ?>
	Are you sure you want to delete the article titled <b><?php echo $ArticleTitle; ?></b>?
	<br /><br />
	<form method="POST" action="<?php echo $SubmitHREF; ?>">
		<input type="submit" name="action" value="Yes" />&nbsp;
		<input type="submit" name="action" value="No" />
	</form><?php
} else { ?>
	Are you sure you want to delete the paper titled <b><?php echo $PaperTitle; ?></b>?<?php
	if (!empty($Articles)) { ?>
		This paper contains the following articles:
		<ul><?php
			foreach ($Articles as $Article) { ?>
				<li><?php echo $Article; ?></li><?php
			} ?>
		</ul><?php
	} else { ?>
		This paper contains no articles.<br /><br /><?php
	} ?>
	<form method="POST" action="<?php echo $SubmitHREF; ?>"><?php
		if (!empty($Articles)) { ?>
			Do you want to also delete the articles in this paper?<br />
			<input type="radio" name="delete_articles" value="Yes" />Yes<br />
			<input type="radio" name="delete_articles" value="No" />No<br /><br /><?php
		} else { ?>
			<input type="hidden" name="delete_articles" value="No" /><?php
		} ?>
		<input type="submit" name="action" value="Yes" />&nbsp;
		<input type="submit" name="action" value="No" />
	</form><?php
} ?>
