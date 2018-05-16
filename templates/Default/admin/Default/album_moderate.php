<?php
if (empty($Entry)) {
	if (empty($Approved)) { ?>
		<p>There are no entries that can be moderated at this time.</p><?php
	} else { ?>
		<p>Select the entry you wish to edit:</p>

		<form method="POST" action="<?php echo $ModerateHREF; ?>">
			<select class="InputFields" name="account_id"><?php
				foreach ($Approved as $AccountID => $Name) { ?>
					<option value="<?php echo $AccountID; ?>"><?php echo $Name; ?></option><?php
				} ?>
			</select>
			&nbsp;
			<input type="submit" value="Submit" />
		</form><?php
	}
} else { ?>

	<a href="<?php echo $ModerateHREF; ?>">&lt;&lt; Back</a>
	<table class="nobord" cellpadding="5" cellspacing="0">
		<tr>
			<td align="center" colspan="3">
				<span style="font-size:150%;"><b>Album&nbsp;Nickname:</b> <?php echo $Entry['nickname']; ?></span>
			</td>
		</tr>

		<tr>
			<form method="POST" action="<?php echo $ResetImageHREF; ?>">
				<td class="center"><?php
					if ($Entry['disabled']) { ?>
						Already<br />Disabled<?php
					} else { ?>
						<input type="submit" name="action" value="Disable" /><?php
					} ?>
				</td>
				<td colspan="2">
					<img src="<?php echo $Entry['upload']; ?>" />
				</td>
				<td style="font-size:75%;">
					You can edit the text that will be sent<br />
					to that user as an email if you reset his picture!<br /><br />
					<textarea spellcheck="true" name="email_txt" id="InputFields" style="width:300;height:200;"><?php echo $DisableEmail; ?></textarea>
				</td>
			</form>
		</tr>

		<tr>
			<td class="center">
				<a href="<?php echo $ResetLocationHREF; ?>" class="submitStyle">Reset</a>
			</td>
			<td>
				<b>Location : </b><?php echo $Entry['location']; ?>
			</td>
		</tr>

		<tr>
			<td class="center">
				<a href="<?php echo $ResetEmailHREF; ?>" class="submitStyle">Reset</a>
			</td>
			<td>
				<b>E-mail : </b><?php echo $Entry['email']; ?>
			</td>
		</tr>

		<tr>
			<td class="center">
				<a href="<?php echo $ResetWebsiteHREF; ?>" class="submitStyle">Reset</a>
			</td>
			<td>
				<b>Website : </b><?php echo $Entry['website']; ?>
			</td>
		</tr>

		<tr>
			<td class="center">
				<a href="<?php echo $ResetBirthdateHREF; ?>" class="submitStyle">Reset</a>
			</td>
			<td>
				<b>Birthdate : </b><?php echo $Entry['birthdate']; ?>
			</td>
		</tr>

		<tr>
			<td class="center">
				<a href="<?php echo $ResetOtherHREF; ?>" class="submitStyle">Reset</a>
			</td>
			<td>
				<b>Other&nbsp;Info : </b><?php echo $Entry['other']; ?>
			</td>
		</tr>

		<form method="POST" action="<?php echo $DeleteCommentHREF; ?>">
			<tr>
				<td></td>
				<td colspan="3"><u>Comments</u></td>
			</tr><?php
			foreach ($Comments as $Comment) { ?>
				<tr>
					<td class="center">
						<input type="checkbox" name="comment_ids[]" value="<?php echo $Comment['id']; ?>">
					</td>
					<td colspan="3">
						<span style="font-size:85%;">[<?php echo $Comment['date']; ?>] &lt;<?php echo $Comment['postee']; ?>&gt; <?php echo $Comment['msg']; ?></span>
					</td>
				</tr><?php
			} ?>
			<tr>
				<td class="center">
					<input type="submit" name="action" value="Delete" />
				</td>
			</tr>
		</form>

	</table><?php
} ?>
