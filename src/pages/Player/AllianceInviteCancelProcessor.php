<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Smr\Page\PlayerPageProcessor;
use SmrInvitation;

class AllianceInviteCancelProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly SmrInvitation $invite
	) {}

	public function build(AbstractSmrPlayer $player): never {
		// Delete the alliance invitation
		$this->invite->delete();
		(new AllianceInvitePlayer())->go();
	}

}
