<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Messages;
use Smr\Page\AccountPage;
use Smr\Template;

class ReportedMessageReply extends AccountPage {

	public function __construct(
		private readonly int $offenderAccountID,
		private readonly int $offendedAccountID,
		private readonly int $gameID,
		private readonly ?string $offenderPreview = null,
		private readonly ?int $offenderBanPoints = null,
		private readonly ?string $offendedPreview = null,
		private readonly ?int $offendedBanPoints = null,
	) {}

	public function build(Account $account, Template $template): void {
		$template->pageTopic = 'Reply To Reported Messages';

		$container = new ReportedMessageReplyProcessor(
			gameID: $this->gameID,
			offenderAccountID: $this->offenderAccountID,
			offendedAccountID: $this->offendedAccountID,
		);

		$offender = Messages::getMessagePlayer($this->offenderAccountID, $this->gameID);
		if (is_object($offender)) {
			$offender = $offender->getDisplayName() . ' (Login: ' . $offender->getAccount()->getLogin() . ')';
		}

		$offended = Messages::getMessagePlayer($this->offendedAccountID, $this->gameID);
		if (is_object($offended)) {
			$offended = $offended->getDisplayName() . ' (Login: ' . $offended->getAccount()->getLogin() . ')';
		}

		$template->pageRenderer = fn() => ReportedMessageReplyRenderer::render(
			NotifyReplyFormPage: $container,
			Offender: $offender,
			Offended: $offended,
			PreviewOffender: $this->offenderPreview,
			OffenderBanPoints: $this->offenderBanPoints,
			PreviewOffended: $this->offendedPreview,
			OffendedBanPoints: $this->offendedBanPoints,
		);
	}

}
