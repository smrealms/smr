<?php declare(strict_types=1);

use Smr\ScoutMessageGroupType;

/**
 * @var Smr\Player $ThisPlayer
 * @var ?string $PreferencesFormHREF
 * @var array{UnreadMessages: int, TotalMessages: int, Type: int, Name: string, DeleteFormHref: string, NumberMessages: int, Messages: array<mixed>, ShowAllHref?: string, GroupedMessages?: array<mixed>} $MessageBox
 */

$styleGreen = ['style' => 'background-color:green'];
if ($MessageBox['Type'] === MSG_GLOBAL) { ?>
	<form name="FORM" method="POST" action="<?php echo $PreferencesFormHREF; ?>">
		<div class="center">Ignore global messages?&nbsp;&nbsp;
			<?php echo create_submit('ignore_globals', 'Yes', fields: ($ThisPlayer->isIgnoreGlobals() ? $styleGreen : [])); ?>&nbsp;
			<?php echo create_submit('ignore_globals', 'No', fields: ($ThisPlayer->isIgnoreGlobals() ? [] : $styleGreen)); ?>
		</div>
	</form><?php
} elseif ($MessageBox['Type'] === MSG_SCOUT) { ?>
	<form name="FORM" method="POST" action="<?php echo $PreferencesFormHREF; ?>">
		<div class="center">
			Group scout messages?&nbsp;&nbsp;<?php
			foreach (ScoutMessageGroupType::cases() as $groupType) {
				echo create_submit('group_scouts', $groupType->value, $groupType->name, fields: ($ThisPlayer->getScoutMessageGroupType() === $groupType ? $styleGreen : []));
			} ?>
		</div>
	</form><?php
} ?>
<br />
<form name="MessageDeleteForm" method="POST" action="<?php echo $MessageBox['DeleteFormHref']; ?>">
	<table class="fullwidth center">
		<tr>
			<td style="width: 30%" valign="middle"><?php
				if (isset($PreviousPageHREF)) {
					?><a href="<?php echo $PreviousPageHREF; ?>"><img src="images/album/rew.jpg" alt="Previous Page" border="0"></a><?php
				} ?>
			</td>
			<td>
				<?php echo create_submit('action', 'Delete'); ?>&nbsp;<select name="action" size="1">
					<option>Marked Messages</option>
					<option>All Messages</option>
				</select>
				<p>You have <span class="yellow"><?php echo $MessageBox['TotalMessages']; ?></span> <?php echo pluralise($MessageBox['TotalMessages'], 'message', false); if ($MessageBox['TotalMessages'] !== $MessageBox['NumberMessages']) { ?> (<?php echo $MessageBox['NumberMessages']; ?> displayed)<?php } ?>.</p>
			</td>
			<td style="width: 30%" valign="middle"><?php
				if (isset($NextPageHREF)) {
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
				if (isset($MessageBox['GroupedMessages'])) {
					$InputName = 'group_id[]';
				} else {
					$InputName = 'message_id[]';
				} ?>
				<tr>
					<td width="10"><input type="checkbox" name="<?php echo $InputName; ?>" value="<?php echo $Message['ID']; ?>" /><?php if ($Message['Unread']) { ?>*<?php } ?></td>
					<td><?php echo bbify($Message['Text']); ?></td>
					<td class="noWrap"><?php echo $Message['SendTime']; ?></td>
				</tr><?php
				if (isset($MessageBox['GroupedMessages'])) { ?>
					<tr>
						<td colspan="3"><?php
							$SubMessages = $MessageBox['GroupedMessages'][$Message['SenderID']]; ?>
							<div class="shrink noWrap pointer" id="toggle-recent<?php echo $Message['SenderID']; ?>" onclick="toggleScoutGroup(<?php echo $Message['SenderID']; ?>);">
								Show/Hide Recent (<?php echo count($SubMessages); ?>)
							</div>
							<table id="group<?php echo $Message['SenderID']; ?>" class="standard fullwidth" style="display:none;margin:5px 0 2px 0;"><?php
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
						} else {
							?>From: <?php echo $Message['SenderDisplayName'];
						} ?>
					</td>
					<td class="noWrap"<?php if (!isset($Message['ReplyHref'])) { ?> colspan="4"<?php } ?>>Date: <?php echo $Message['SendTime']; ?></td>
					<?php
					if (isset($Message['ReplyHref'])) { ?>
						<td>
							<a href="<?php echo $Message['ReportHref']; ?>"><img class="bottom" src="images/report.png" width="16" height="16" border="0" title="Report this message to an admin" /></a>
						</td>
						<td>
							<a href="<?php echo $Message['BlacklistHref']; ?>">Blacklist Player</a>
						</td>
						<td>
							<a href="<?php echo $Message['ReplyHref']; ?>">Reply</a>
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
			if (isset($PreviousPageHREF)) {
				?><a href="<?php echo $PreviousPageHREF; ?>"><img src="images/album/rew.jpg" alt="Previous Page" border="0"></a><?php
			} ?>
		</td>
		<td>
		</td>
		<td style="width: 30%" valign="middle"><?php
			if (isset($NextPageHREF)) {
				?><a href="<?php echo $NextPageHREF; ?>"><img src="images/album/fwd.jpg" alt="Next Page" border="0"></a><?php
			} ?>
		</td>
	</tr>
</table>
