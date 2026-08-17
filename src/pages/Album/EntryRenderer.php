<?php declare(strict_types=1);

/**
 * @var array{Nick: string, PageViews: int, ImgSrc: string, Location: string, Email: string, Website: string, Birthdate: string, OtherInfo: string, AccountID: int} $Entry
 * @var array<array{id: int, date: string, commenter: string, msg: string}> $Comments
 */

?>
<table border="0" cellpadding="5" cellspacing="0">
	<tr>
		<td colspan="2">
			<div style="margin-left: auto; margin-right: auto; width: 50%">
				<table style="width: 100%">
					<tr>
						<td class="center" style="width: 30%" valign="middle"><?php
							if (isset($PrevNick)) { ?>
								<a href="?nick=<?php echo urlencode($PrevNick); ?>">
									<img src="/images/album/rew.jpg" alt="<?php echo htmlentities($PrevNick); ?>" border="0">
								</a>&nbsp;&nbsp;&nbsp;<?php
							} ?>
						</td>
						<td class="center noWrap" valign="middle">
							<span style="font-size: 150%;"><?php echo htmlentities($Entry['Nick']); ?></span>
							<br />
							<span style="font-size: 75%;">Views: <?php echo $Entry['PageViews']; ?></span>
						</td>
						<td class="center" style="width: 30%" valign="middle"><?php
							if (isset($NextNick)) { ?>
								&nbsp;&nbsp;&nbsp;
								<a href="?nick=<?php echo urlencode($NextNick); ?>">
									<img src="/images/album/fwd.jpg" alt="<?php echo htmlentities($NextNick); ?>" border="0">
								</a><?php
							} ?>
						</td>
					</tr>
				</table>
			</div>
		</td>
	</tr>

	<tr>
		<td colspan="2" class="center" valign="middle">
			<img src="../<?php echo $Entry['ImgSrc']; ?>" />
		</td>
	</tr>

	<tr>
		<td class="right bold" width="10%">Location:</td>
		<td><?php echo $Entry['Location']; ?></td>
	</tr>

	<tr>
		<td class="right bold" width="10%">E-mail:</td>
		<td><?php echo $Entry['Email']; ?></td>
	</tr>

	<tr>
		<td class="right bold" width="10%">Website:</td>
		<td><?php echo $Entry['Website']; ?></td>
	</tr>

	<tr>
		<td class="right bold" width="10%">Birthdate:</td>
		<td><?php echo $Entry['Birthdate']; ?></td>
	</tr>

	<tr>
		<td class="right bold top" width="10%">Other&nbsp;Info:</td>
		<td><?php echo $Entry['OtherInfo']; ?></td>
	</tr>

	<tr>
		<td colspan="2">
			<p><u>Comments</u></p><?php
			foreach ($Comments as $Comment) { ?>
				<span style="font-size: 85%;">[<?php echo $Comment['date']; ?>] <b>&lt;<?php echo $Comment['commenter']; ?>&gt;</b> <?php echo $Comment['msg']; ?></span><br /><?php
			}

			if (isset($ViewerDisplayName)) { ?>
				<form action="album_comment_processing.php">
					<input type="hidden" name="album_id" value="<?php echo $Entry['AccountID']; ?>" />
					<input type="hidden" name="album_nick" value="<?php echo $Entry['Nick']; ?>" />
					<table class="dgreen" style="font-size: 70%;">
						<tr>
							<td>
								Nick:<br />
								<input type="text" size="10" name="nick" value="<?php echo $ViewerDisplayName; ?>" readonly />
							</td>
							<td>
								Comment:<br />
								<input type="text" size="50" name="comment" required />
							</td>
							<td>
								<br />
								<?php echo create_submit('action', 'Send'); ?>
							</td><?php
							if (isset($CanModerate) && $CanModerate) { ?>
								<td>
									<br />
									<?php echo create_submit('action', 'Moderate', fields: ['formnovalidate' => true]); ?>
								</td><?php
							} ?>
						</tr>
					</table>
				</form><?php
			} else { ?>
				<p>Please <a href="/login.php"><u>login</u></a> if you want comment on this picture!</p><?php
			} ?>
		</td>
	</tr>
</table>
