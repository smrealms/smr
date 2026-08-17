<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Exception;
use Smr\Player;
use Smr\ScoutMessageGroupType;

class MessageViewRenderer {

	/**
	 * @param array{UnreadMessages: int, TotalMessages: int, Type: int, Name: string, DeleteFormHref: string, NumberMessages: int, Messages: array<PlayerMessage>, ShowAllHref?: string} $MessageBox
	 */
	public static function render(
		?string $PreviousPageHREF,
		?string $NextPageHREF,
		?MessagePreferenceIgnoreGlobalsProcessor $PreferencesIgnoreGlobalsPage,
		?MessagePreferenceScoutGroupProcessor $PreferencesScoutGroupPage,
		array $MessageBox,
		Player $ThisPlayer,
	): void {
		$styleGreen = ['style' => 'background-color:green'];
		if ($MessageBox['Type'] === MSG_GLOBAL) {
			if ($PreferencesIgnoreGlobalsPage === null) {
				throw new Exception('Expected non-null PreferencesIgnoreGlobalsPage');
			} ?>
			<form name="FORM" method="POST" action="<?php echo $PreferencesIgnoreGlobalsPage->href(); ?>">
				<div class="center">Ignore global messages?&nbsp;&nbsp;
					<?php echo $PreferencesIgnoreGlobalsPage->actionYes->html(fields: ($ThisPlayer->isIgnoreGlobals() ? $styleGreen : [])); ?>&nbsp;
					<?php echo $PreferencesIgnoreGlobalsPage->actionNo->html(fields: ($ThisPlayer->isIgnoreGlobals() ? [] : $styleGreen)); ?>
				</div>
			</form><?php
		} elseif ($MessageBox['Type'] === MSG_SCOUT) {
			if ($PreferencesScoutGroupPage === null) {
				throw new Exception('Expected non-null PreferencesScoutGroupPage');
			} ?>
			<form name="FORM" method="POST" action="<?php echo $PreferencesScoutGroupPage->href(); ?>">
				<div class="center">
					Group scout messages?&nbsp;&nbsp;<?php
					foreach (ScoutMessageGroupType::cases() as $groupType) {
						echo $PreferencesScoutGroupPage->actionScoutGroup[$groupType->value]->html($groupType->name, ($ThisPlayer->getScoutMessageGroupType() === $groupType ? $styleGreen : []));
					} ?>
				</div>
			</form><?php
		} ?>
		<br />
		<form name="MessageDeleteForm" method="POST" action="<?php echo $MessageBox['DeleteFormHref']; ?>">
			<table class="fullwidth center">
				<tr>
					<td style="width: 30%" valign="middle"><?php
						if ($PreviousPageHREF !== null) {
							?><a href="<?php echo $PreviousPageHREF; ?>"><img src="images/album/rew.jpg" alt="Previous Page" border="0"></a><?php
						} ?>
					</td>
					<td>
						<?php echo create_submit_display('Delete'); ?>&nbsp;<select name="marked_or_all" size="1">
							<option>Marked Messages</option>
							<option>All Messages</option>
						</select>
						<p>You have <span class="yellow"><?php echo $MessageBox['TotalMessages']; ?></span> <?php echo pluralise($MessageBox['TotalMessages'], 'message', false); if ($MessageBox['TotalMessages'] !== $MessageBox['NumberMessages']) { ?> (<?php echo $MessageBox['NumberMessages']; ?> displayed)<?php } ?>.</p>
					</td>
					<td style="width: 30%" valign="middle"><?php
						if ($NextPageHREF !== null) {
							?><a href="<?php echo $NextPageHREF; ?>"><img src="images/album/fwd.jpg" alt="Next Page" border="0"></a><?php
						} ?>
					</td>
				</tr>
			</table><?php

			if (isset($MessageBox['ShowAllHref'])) {
				?><div class="buttonA"><a class="buttonA" href="<?php echo $MessageBox['ShowAllHref'] ?>">Show all Messages</a></div><br /><br /><?php
			} ?>
			<table class="standard fullwidth"><?php
				foreach ($MessageBox['Messages'] as $Message) {
					if ($MessageBox['Type'] === MSG_SCOUT) {
						if (isset($Message['GroupedMessages'])) {
							$InputName = 'group_id[]';
						} else {
							$InputName = 'message_id[]';
						} ?>
						<tr>
							<td width="10"><input type="checkbox" name="<?php echo $InputName; ?>" value="<?php echo $Message['ID']; ?>" /><?php if ($Message['Unread']) { ?>*<?php } ?></td>
							<td><?php echo bbify($Message['Text']); ?></td>
							<td class="noWrap"><?php echo $Message['SendTime']; ?></td>
						</tr><?php
						if (isset($Message['GroupedMessages'])) { ?>
							<tr>
								<td colspan="3"><?php
									$SubMessages = $Message['GroupedMessages']; ?>
									<div class="shrink noWrap pointer" onclick="toggleScoutGroup('<?php echo $Message['ID']; ?>');">
										Show/Hide Recent (<?php echo count($SubMessages); ?>)
									</div>
									<table id="group<?php echo $Message['ID']; ?>" class="standard fullwidth" style="display:none;margin:5px 0 2px 0;"><?php
										foreach ($SubMessages as $SubMessage) { ?>
											<tr>
												<td width="10"><input type="checkbox" name="message_id[]" value="<?php echo $SubMessage['ID']; ?>" /><?php if ($SubMessage['Unread']) { ?>*<?php } ?></td>
												<td><?php echo bbify($SubMessage['Text']); ?></td>
												<td class="noWrap"><?php echo $SubMessage['SendTime']; ?></td>
											</tr><?php
										} ?>
									</table>
								</td>
							</tr><?php
						}
					} else { ?>
						<tr>
							<td width="10"><input type="checkbox" name="message_id[]" value="<?php echo $Message['ID']; ?>" /><?php if ($Message['Unread']) { ?>*<?php } ?></td>
							<td class="noWrap" width="100%"><?php
								if (isset($Message['ReceiverDisplayName'])) {
									?>To: <?php echo $Message['ReceiverDisplayName'];
								} elseif (isset($Message['SenderDisplayName'])) {
									?>From: <?php echo $Message['SenderDisplayName'];
								} ?>
							</td>
							<td class="noWrap"<?php if (!isset($Message['Actions'])) { ?> colspan="4"<?php } ?>>Date: <?php echo $Message['SendTime']; ?></td>
							<?php
							if (isset($Message['Actions'])) { ?>
								<td>
									<a href="<?php echo $Message['Actions']['ReportHref']; ?>"><img class="bottom" src="images/report.png" width="16" height="16" border="0" title="Report this message to an admin" /></a>
								</td>
								<td>
									<a href="<?php echo $Message['Actions']['BlacklistHref']; ?>">Blacklist Player</a>
								</td>
								<td>
									<a href="<?php echo $Message['Actions']['ReplyHref']; ?>">Reply</a>
								</td><?php
							} ?>
						</tr>
						<tr>
							<td colspan="6"><?php echo bbify($Message['Text']); ?></td>
						</tr><?php
					}
				} ?>
			</table>
		</form>

		<table class="fullwidth center">
			<tr>
				<td style="width: 30%" valign="middle"><?php
					if ($PreviousPageHREF !== null) {
						?><a href="<?php echo $PreviousPageHREF; ?>"><img src="images/album/rew.jpg" alt="Previous Page" border="0"></a><?php
					} ?>
				</td>
				<td>
				</td>
				<td style="width: 30%" valign="middle"><?php
					if ($NextPageHREF !== null) {
						?><a href="<?php echo $NextPageHREF; ?>"><img src="images/album/fwd.jpg" alt="Next Page" border="0"></a><?php
					} ?>
				</td>
			</tr>
		</table>

		<?php
	}

}
