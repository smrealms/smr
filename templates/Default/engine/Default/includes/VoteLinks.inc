<?php
if (SmrSession::hasGame()) { ?>
	<div>Get <b><u>FREE TURNS</u></b> for voting if you see the star, available <span id="v"><?php echo $TimeToNextVote ?></span>.</div><?php
} ?>
<span id="vote_links"><?php
	foreach ($VoteSites as $VoteSite) { ?>
		<a href='<?php echo $VoteSite['url']; ?>' target="_blank">
			<img class="vote_site" src="images/game_sites/<?php echo $VoteSite['img']; ?>" alt="" width="98" height="41" />
		</a><?php
	} ?>
</span>
