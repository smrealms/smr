<?php declare(strict_types=1);

/**
 * @var Smr\Account $ThisAccount
 * @var string $AlbumEditHref
 * @var string $AlbumDeleteHref
 * @var array{Location: string, Email: string, Website: string, Day: int|'', Month: int|'', Year: int|'', Other: string, Status: string, Image?: string} $AlbumEntry
 */

?>
<p><span style="font-size:80%;">Here you have a chance to add an entry to the Space Merchant Realms - The Photo Album!<br />
We only accept jpg or gif images to a maximum of 500 x 500 in size.<br />
Your image will be posted under your <i>Hall Of Fame</i> nick!<br />
<b>Please Note:</b> Your entry needs to be approved by an admin before going online</span></p>

<p style="font-size:150%;">
	Status of your album entry: <?php echo $AlbumEntry['Status']; ?>
</p>

<form name="AlbumEditForm" enctype="multipart/form-data" method="POST" action="<?php echo $AlbumEditHref; ?>">
	<table>
		<tr>
			<td class="right bold">Nick:</td>
			<td><?php echo $ThisAccount->getHofDisplayName(); ?></td>
		</tr>

		<tr>
			<td class="right bold">Location:</td>
			<td><input type="text" name="location" value="<?php echo htmlspecialchars($AlbumEntry['Location']); ?>" /></td>
		</tr>

		<tr>
			<td class="right bold">Email Address:</td>
			<td><input type="email" name="email" value="<?php echo htmlspecialchars($AlbumEntry['Email']); ?>" style="width:303px;"></td>
		</tr>

		<tr>
			<td class="right bold">Website:</td>
			<td><input type="url" name="website" style="width:303px;" value="<?php echo htmlspecialchars($AlbumEntry['Website']); ?>"></td>
		</tr>

		<tr>
			<td class="right bold">Birthdate:</td>
			<td>Day:&nbsp;<input type="number" name="day" class="center" value="<?php echo $AlbumEntry['Day']; ?>" min="1" max="31">&nbsp;&nbsp;&nbsp;
				Month:&nbsp;<input type="number" name="month" class="center" value="<?php echo $AlbumEntry['Month']; ?>" min="1" max="12">&nbsp;&nbsp;&nbsp;
				Year:&nbsp;<input type="number" name="year" class="center" value="<?php echo $AlbumEntry['Year']; ?>" min="1900" max="<?php echo date('Y'); ?>">
			</td>
		</tr>

		<tr>
			<td class="right bold">Other Info:</td>
			<td><textarea spellcheck="true" name="other" style="width:303px;height:100px;"><?php echo htmlentities($AlbumEntry['Other']); ?></textarea></td>
		</tr>

		<tr>
			<td valign="top" class="right bold">Image:</td>
			<td>
				<?php if (isset($AlbumEntry['Image'])) { ?><img src="<?php echo $AlbumEntry['Image']; ?>"><br /><?php } ?>
				<input type="file" name="photo" accept="image/jpeg, image/png" style="width:303px;" >
			</td>
		</tr>

		<tr>
			<td>&nbsp;</td>
			<td>
				<input type="submit" name="action" value="Submit" />&nbsp;&nbsp;&nbsp;
				<a href="<?php echo $AlbumDeleteHref; ?>" class="submitStyle">Delete Entry</a>
			</td>
		</tr>

		<?php
		if (isset($SuccessMsg)) { ?>
			<tr>
				<td></td>
				<td><p class="green"><?php echo $SuccessMsg; ?></p></td>
			</tr><?php
		} ?>

	</table>
</form>
