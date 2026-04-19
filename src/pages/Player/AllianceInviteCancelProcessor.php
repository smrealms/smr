<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AllianceInvite;
use Smr\Page\PlayerPageProcessor;
use Smr\Player;

class AllianceInviteCancelProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly AllianceInvite $invite,
	) {}

	public function build(Player $player): never {
		// Delete the alliance invitation
		$this->invite->delete();
		(new AllianceInvitePlayer())->go();
	}

}
