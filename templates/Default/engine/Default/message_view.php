<?php
if (isset($MessageBoxes))
{ ?>
	<p>Please choose your Message folder!</p>

	<table class="standard">
		<tr>
			<th>Folder</th>
			<th>Messages</th>
			<th>&nbsp;</th>
		</tr><?php
		foreach ($MessageBoxes as $MessageBox)
		{ ?>
			<tr<?php if($MessageBox['HasUnread']) { ?>  class="bold"<?php } ?>>
				<td>
					<a href="<?php echo $MessageBox['ViewHref']; ?>"><?php echo $MessageBox['Name']; ?></a>
				</td>
				<td align="center" class="yellow"><?php echo $MessageBox['MessageCount']; ?></td>
				<td><a href="<?php echo $MessageBox['DeleteHref']; ?>">Empty</a></td>
			</tr><?php
		} ?>
	</table>
	<p><a href="<?php echo $ManageBlacklistLink; ?>">Manage Player Blacklist</a></p><?php
}
else
{
	if ($MessageBox['Type'] == MSG_GLOBAL)
	{ ?>
		<form name="IgnoreGlobalsForm" method="POST" action="<?php echo $IgnoreGlobalsFormHref; ?>">
			<div align="center">Ignore global messages?&nbsp;&nbsp;
				<input type="submit" name="action" value="Yes" id="InputFields"<?php if ($ThisPlayer->isIgnoreGlobals()) { ?> style="background-color:green;"<?php } ?> />&nbsp;<input type="submit" name="action" value="No" id="InputFields"<?php if (!$ThisPlayer->isIgnoreGlobals()) { ?> style="background-color:green;"<?php } ?> />
			</div>
		</form><?php
	} ?>
	<br />
	<form name="MessageDeleteForm" method="POST" action="<?php echo $MessageBox['DeleteFormHref']; ?>">
		<table class="fullwidth center">
			<tr>
				<td style="width: 30%" valign="middle"><?php
					if(isset($PreviousPageHREF))
					{
						?><a href="<?php echo $PreviousPageHREF; ?>"><img src="<?php echo URL; ?>/images/album/rew.jpg" alt="Previous Page" border="0"></a><?php
					} ?>
				</td>
				<td>
					<input type="submit" name="action" value="Delete" id="InputFields" />&nbsp;<select name="action" size="1" id="InputFields">
																						<option>Marked Messages</option>
																						<option>All Messages</option>
																					</select>
					<p>You have <span class="yellow"><?php echo $MessageBox['TotalMessages']; ?></span> message<?php if($MessageBox['TotalMessages']!=1) { ?>s<?php } if($MessageBox['TotalMessages']!=$MessageBox['NumberMessages']){ ?> of which <span class="yellow"><?php echo $MessageBox['NumberMessages']; ?></span> <?php if($MessageBox['NumberMessages'] == 1){ ?>is<?php }else{ ?>are<?php } ?> being displayed<?php } ?>.</p>
				</td>
				<td style="width: 30%" valign="middle"><?php
					if(isset($NextPageHREF))
					{
						?><a href="<?php echo $NextPageHREF; ?>"><img src="<?php echo URL; ?>/images/album/fwd.jpg" alt="Next Page" border="0"></a><?php
					} ?>
				</td>
			</tr>
		</table><?php
		
		if (isset($MessageBox['ShowAllHref']))
		{
			?><div class="buttonA"><a class="buttonA" href="<?php echo $MessageBox['ShowAllHref'] ?>">&nbsp;Show all Messages&nbsp;</a></div><br /><br /><?php
		} ?>
		<table class="standard fullwidth"><?php
			if(isset($MessageBox['Messages']))
			{
				foreach($MessageBox['Messages'] as &$Message)
				{ ?>
					<tr>
						<td width="10"><input type="checkbox" name="message_id[]" value="<?php echo $Message['ID']; ?>" /><?php if($Message['Unread']) { ?>*<?php } ?></td>
						<td class="noWrap" width="100%"><?php
							if(isset($Message['RecieverDisplayName']))
							{
								?>To: <?php echo $Message['RecieverDisplayName'];
							}
							else
							{
								?>From: <?php echo $Message['SenderDisplayName'];
							} ?>
						</td>
						<td class="noWrap"<?php if(!isset($Message['Sender'])) { ?> colspan="3"<?php } ?>>Date: <?php echo date(DATE_FULL_SHORT, $Message['SendTime']); ?></td>
						<td>
							<a href="<?php echo $Message['ReportHref']; ?>"><img src="images/notify.gif" border="0" align="right" title="Report this message to an admin" /></a>
						</td><?php
						if (isset($Message['Sender']))
						{ ?>
							<td>
								<a href="<?php echo $Message['BlacklistHref']; ?>">Blacklist Player</a>
							</td>
							<td>
								<a href="<?php echo $Message['ReplyHref']; ?>">Reply</a>
							</td><?php
						} ?>
					</tr>
					<tr>
						<td colspan="6"><?php echo bbifyMessage($Message['Text']); ?></td>
					</tr>
					<?php
				} unset($Message);
			}
			if(isset($MessageBox['GroupedMessages']))
			{
				foreach($MessageBox['GroupedMessages'] as &$Message)
				{ ?>
					<tr>
						<td width="10"><input type="checkbox" name="message_id[]" value="<?php echo $Message['ID'] ?>" /><?php if($Message['Unread']) { ?>*<?php } ?></td>
						<td class="noWrap" width="100%">From: <?php echo $Message['SenderDisplayName']; ?></td>
						<td class="noWrap" colspan="4">Date: <?php echo date(DATE_FULL_SHORT, $Message['FirstSendTime']); ?> - <?php echo date(DATE_FULL_SHORT, $Message['LastSendTime']); ?></td>
					</tr>
					<tr>
						<td colspan="6"><?php echo bbifyMessage($Message['Text']); ?></td>
					</tr>
					<?php
				} unset($Message);
			} ?>
		</table>
		<table class="fullwidth center">
			<tr>
				<td style="width: 30%" valign="middle"><?php
					if(isset($PreviousPageHREF))
					{
						?><a href="<?php echo $PreviousPageHREF; ?>"><img src="<?php echo URL; ?>/images/album/rew.jpg" alt="Previous Page" border="0"></a><?php
					} ?>
				</td>
				<td>
				</td>
				<td style="width: 30%" valign="middle"><?php
					if(isset($NextPageHREF))
					{
						?><a href="<?php echo $NextPageHREF; ?>"><img src="<?php echo URL; ?>/images/album/fwd.jpg" alt="Next Page" border="0"></a><?php
					} ?>
				</td>
			</tr>
		</table>
	</form><?php
} ?>