<?php declare(strict_types=1);

use Smr\Session;

/**
 * @var array<array{img: string, url: string, sn: string|false}> $VoteLinks
 * @var string $TimeToNextVote
 */

if (Session::getInstance()->hasGame()) { ?>
	<div>Get <b><u>FREE TURNS</u></b> for voting if you see the star, available <span id="v"><?php echo $TimeToNextVote ?></span>.</div><?php
} ?>
<span id="vote_links"><?php
	foreach ($VoteLinks as $VoteLink) { ?>
		<a href='<?php echo htmlspecialchars($VoteLink['url']); ?>' target="_blank" <?php if ($VoteLink['sn'] !== false) { ?> data-sn="<?php echo $VoteLink['sn']; ?>" onclick="voteSite(this.dataset.sn)" <?php } ?>>
			<img class="vote_site" src="images/game_sites/<?php echo $VoteLink['img']; ?>" alt="" width="98" height="41" />
		</a><?php
	} ?>
</span>
