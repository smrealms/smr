<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Messages;
use Smr\Page\AccountPage;
use Smr\Template;
use SmrAccount;

class ReportedMessageReply extends AccountPage {

	public string $file = 'admin/notify_reply.php';

	public function __construct(
		private readonly int $offenderAccountID,
		private readonly int $offendedAccountID,
		private readonly int $gameID,
		private readonly ?string $offenderPreview = null,
		private readonly ?int $offenderBanPoints = null,
		private readonly ?string $offendedPreview = null,
		private readonly ?int $offendedBanPoints = null
	) {}

	public function build(SmrAccount $account, Template $template): void {
		$template->assign('PageTopic', 'Reply To Reported Messages');

		$container = new ReportedMessageReplyProcessor(
			gameID: $this->gameID,
			offenderAccountID: $this->offenderAccountID,
			offendedAccountID: $this->offendedAccountID
		);
		$template->assign('NotifyReplyFormHref', $container->href());

		$offender = Messages::getMessagePlayer($this->offenderAccountID, $this->gameID);
		if (is_object($offender)) {
			$offender = $offender->getDisplayName() . ' (Login: ' . $offender->getAccount()->getLogin() . ')';
		}
		$template->assign('Offender', $offender);

		$offended = Messages::getMessagePlayer($this->offendedAccountID, $this->gameID);
		if (is_object($offended)) {
			$offended = $offended->getDisplayName() . ' (Login: ' . $offended->getAccount()->getLogin() . ')';
		}
		$template->assign('Offended', $offended);

		$template->assign('PreviewOffender', $this->offenderPreview);
		$template->assign('OffenderBanPoints', $this->offenderBanPoints);

		$template->assign('PreviewOffended', $this->offendedPreview);
		$template->assign('OffendedBanPoints', $this->offendedBanPoints);
	}

}
