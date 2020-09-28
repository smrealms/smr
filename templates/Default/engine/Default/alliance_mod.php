<div class="center">

<?php
if (isset($OpTime)) { ?>
	<table class="center nobord opResponse">
		<tr><th>ENCRYPTED ALLIANCE TELEGRAM</th></tr>
		<tr><td>Your leader has scheduled an important alliance operation for <?php echo date(DATE_FULL_SHORT, $OpTime); ?></td></tr>
		<tr><td><span id="countdown"><?php echo ucfirst(format_time($OpTime - TIME)); ?></span></td></tr>
		<tr><td><b>Will you join the operation?</b></td></tr>
		<tr><td>
			<form method="POST" action="<?php echo $OpResponseHREF; ?>"><?php
				foreach ($ResponseInputs as $option => $style) { ?>
					<span style="padding: 0 4px 0 4px">
						<input type="submit" name="op_response" <?php echo $style; ?> value="<?php echo $option; ?>" />
					</span><?php
				} ?>
			</form>
		</td></tr>
	</table><br /><?php
}

if ($Alliance->hasImageURL()) { ?>
	<img class="alliance" src="<?php echo $Alliance->getImageURL(); ?>" alt="">
	<br /><br /><?php
} ?>

<span class="yellow">Message from your leader</span>
<p><?php echo bbifyMessage($Alliance->getMotD()); ?></p>

<?php
if (isset($EditHREF)) { ?>
	<div class="buttonA">
		<a class="buttonA" href="<?php echo $EditHREF; ?>">Edit</a>
	</div><?php
}

if (!empty($DiscordServer)) { ?>
	<br /><br />
	<iframe src="https://discordapp.com/widget?id=<?php echo $DiscordServer; ?>&amp;theme=dark" width="350" height="375" allowtransparency="true" frameborder="0"></iframe>
	<?php
} ?>

</div>
