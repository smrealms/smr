<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Account;
use Smr\Template;

class AllianceMessageBoardRenderer {

	/**
	 * @param array<int, array{DeleteHref?: string, Replies: int, Sender: string, SendTime: int, ThreadID: int, Topic: string, Unread: bool, ViewHref: string}> $Threads
	 */
	public static function render(
		Template $template,
		array $Threads,
		?AllianceMessageBoardAddProcessor $CreateNewThreadFormPage,
		?string $Preview,
		?string $Topic,
		?bool $AllianceEyesOnly,
		Account $ThisAccount,
	): void {
		if (count($Threads) > 0) { ?>
			<table id="topic-list" class="centered standard inset">
				<thead>
					<tr>
						<th class="sort" data-sort="sort_topic">Topic</th>
						<th class="sort shrink" data-sort="sort_author">Author</th>
						<th class="sort shrink" data-sort="sort_replies">Replies</th>
						<th class="sort shrink" data-sort="sort_lastReply">Last Reply</th>
					</tr>
				</thead>
				<tbody class="list"><?php
					foreach ($Threads as $Thread) { ?>
						<tr id="topic-<?php echo $Thread['ThreadID']; ?>" class="ajax">
							<td class="sort_topic"><?php
								if ($Thread['Unread']) {
									?><b><?php
								}
								?><a href="<?php echo $Thread['ViewHref']; ?>"><?php echo htmlentities($Thread['Topic']); ?></a><?php
								if ($Thread['Unread']) {
									?></b><?php
								} ?>
							</td>
							<td class="sort_author noWrap"><?php
								echo $Thread['Sender'];
								if (isset($Thread['DeleteHref'])) {
									?><br /><small><a href="<?php echo $Thread['DeleteHref']; ?>">Delete Thread!</a></small><?php
								} ?>
							</td>
							<td class="sort_replies center"><?php echo $Thread['Replies']; ?></td>
							<td class="sort_lastReply noWrap" data-lastReply="<?php echo $Thread['SendTime']; ?>"><?php echo date($ThisAccount->getDateTimeFormat(), $Thread['SendTime']); ?></td>
						</tr><?php
					} ?>
				</tbody>
			</table><br /><?php
			$template->listjsInclude = 'alliance_message';
		}

		if ($CreateNewThreadFormPage !== null) { ?>
			<h2>Create Thread</h2><br /><?php
			if ($Preview !== null) { ?><table class="standard"><tr><td><?php echo bbify($Preview); ?></td></tr></table><?php } ?>
			<form class="standard" id="CreateNewThreadForm" method="POST" action="<?php echo $CreateNewThreadFormPage->href(); ?>">
			<table class="standardnobord nohpad">
				<tr>
					<td class="top">Topic:&nbsp;</td>
					<td class="mb"><input type="text" name="topic" required size="30" value="<?php if ($Topic !== null) { echo htmlspecialchars($Topic); } ?>"></td>
					<td>For Alliance Eyes Only:<input name="allEyesOnly" type="checkbox"<?php if ($AllianceEyesOnly !== null && $AllianceEyesOnly) { ?>checked="checked" <?php } ?>></td>
				</tr>
				<tr>
					<td class="top">Body:&nbsp;</td>
					<td colspan="2"><textarea spellcheck="true" name="body" required><?php if ($Preview !== null) { echo $Preview; } ?></textarea></td>
				</tr>
			</table><br />
			<?php echo $CreateNewThreadFormPage->actionCreate->html('New Thread'); ?>&nbsp;<?php echo $CreateNewThreadFormPage->actionPreview->html('Preview Thread'); ?>
			</form><?php
		}

	}

}
