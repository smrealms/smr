<?php declare(strict_types=1);

namespace Smr\Pages\Layout;

class VoteLinksRenderer {

	/**
	 * @param array<array{img: string, url: string, sn: string|false}> $VoteLinks
	 */
	public static function render(array $VoteLinks, ?int $TimeToNextVote): void {
		if ($TimeToNextVote !== null) { ?>
			<div>Get <b><u>FREE TURNS</u></b> for voting if you see the star, available <span id="v"><?php echo in_time_or_now($TimeToNextVote, short: true) ?></span>.</div><?php
		} ?>
		<span id="vote_links"><?php
			foreach ($VoteLinks as $VoteLink) { ?>
				<a href='<?php echo htmlspecialchars($VoteLink['url']); ?>' target="_blank" <?php if ($VoteLink['sn'] !== false) { ?> data-sn="<?php echo $VoteLink['sn']; ?>" onclick="voteSite(this.dataset.sn)" <?php } ?>>
					<img class="vote_site" src="images/game_sites/<?php echo $VoteLink['img']; ?>" alt="" width="98" height="41" />
				</a><?php
			} ?>
		</span>
		<?php
	}

}
