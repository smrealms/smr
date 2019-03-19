<p><span style="font-size:80%;">Here you have a chance to add an entry to the Space Merchant Realms - The Photo Album!<br />
We only accept jpg or gif images to a maximum of 500 x 500 in size.<br />
Your image will be posted under your <i>Hall Of Fame</i> nick!<br />
<b>Please Note:</b> Your entry needs to be approved by an admin before going online</span></p>

<p style="font-size:150%;">
	Status of your album entry: <?php if(!isset($AlbumEntry)) { ?><span style="color:orange;">No entry</span><?php } else { echo $AlbumEntry['Status']; } ?>
</p>

<form name="AlbumEditForm" enctype="multipart/form-data" method="POST" action="<?php echo $AlbumEditHref; ?>">
	<table>
		<tr>
			<td align="right" class="bold">Nick:</td>
			<td><?php echo $ThisAccount->getHofName(); ?></td>
		</tr>
		
		<tr>
			<td align="right" class="bold">Location:</td>
			<td><input type="text" name="location" class="InputFields" value="<?php if(isset($AlbumEntry)){ echo htmlspecialchars($AlbumEntry['Location']); } else { ?>N/A<?php } ?>" onFocus="javascript:if (this.value == 'N/A') {this.value = '';}" onBlur="javascript:if (this.value == '') {this.value = 'N/A';}"></td>
		</tr>
		
		<tr>
			<td align="right" class="bold">Email Address:</td>
			<td><input type="email" name="email" class="InputFields" value="<?php if(isset($AlbumEntry)){ echo htmlspecialchars($AlbumEntry['Email']); } ?>" style="width:303px;"></td>
		</tr>
		
		<tr>
			<td align="right" class="bold">Website:</td>
			<td><input type="url" name="website" class="InputFields" style="width:303px;" value="<?php if(isset($AlbumEntry) && $AlbumEntry['Website'] != ''){ echo htmlspecialchars($AlbumEntry['Website']); } ?>"></td>
		</tr>
		
		<tr>
			<td align="right" class="bold">Birthdate:</td>
			<td>Day:&nbsp;<input type="text" name="month" class="InputFields center" value="<?php if(isset($AlbumEntry)){ echo htmlspecialchars($AlbumEntry['Month']); } else { ?>N/A<?php } ?>" size="3" maxlength="2" onFocus="javascript:if (this.value == 'N/A') {this.value = '';}" onBlur="javascript:if (this.value == '') {this.value = 'N/A';}">&nbsp;&nbsp;&nbsp;
				Month:&nbsp;<input type="text" name="day" class="InputFields center" value="<?php if(isset($AlbumEntry)){ echo htmlspecialchars($AlbumEntry['Day']); } else { ?>N/A<?php } ?>" size="3" maxlength="2" onFocus="javascript:if (this.value == 'N/A') {this.value = '';}" onBlur="javascript:if (this.value == '') {this.value = 'N/A';}">&nbsp;&nbsp;&nbsp;
				Year:&nbsp;<input type="text" name="year" class="InputFields center" value="<?php if(isset($AlbumEntry)){ echo htmlspecialchars($AlbumEntry['Year']); } else { ?>N/A<?php } ?>" size="3" maxlength="4" onFocus="javascript:if (this.value == 'N/A') {this.value = '';}" onBlur="javascript:if (this.value == '') {this.value = 'N/A';}">
			</td>
		</tr>
		
		<tr>
			<td align="right" valign="top" class="bold">Other Info:<br /><small>(AIM/ICQ)</small></td>
			<td><textarea spellcheck="true" name="other" class="InputFields" style="width:303px;height:100px;" onFocus="javascript:if (this.value == 'N/A') {this.value = '';}" onBlur="javascript:if (this.value == '') {this.value = 'N/A';}"><?php if(isset($AlbumEntry)){ echo $AlbumEntry['Other']; } else { ?>N/A<?php } ?></textarea></td>
		</tr>
		
		<tr>
			<td align="right" valign="top" class="bold">Image:</td>
			<td>
				<?php if(isset($AlbumEntry) && isset($AlbumEntry['Image'])) { ?><img src="<?php echo $AlbumEntry['Image']; ?>"><br /><?php } ?>
				<input type="file" name="photo" accept="image/jpeg, image/png" class="InputFields" style="width:303px;" >
			</td>
		</tr>
		
		<tr>
			<td>&nbsp;</td>
			<td>
				<input type="submit" name="action" value="Submit" class="InputFields" />&nbsp;&nbsp;&nbsp;<input type="submit" name="action" value="Delete Entry" class="InputFields" />
			</td>
		</tr>
	</table>
</form>
