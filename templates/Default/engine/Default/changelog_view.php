<?php
if (isset($ContinueHREF)) {
	// Make the login changelog scroll if it is larger than 420px ?>
	<style>div.login_scroll {height: 420px; overflow-y: auto;}</style>

	<div class="buttonA">
		<a class="buttonA" href="<?php echo $ContinueHREF; ?>">Continue</a>
	</div>
	<br /><br />
	<big>Here are the updates that have gone live since your last visit, enjoy!</big>
	<br /><br /><?php
} ?>

<div class="login_scroll"><?php
	foreach ($Versions as $data) { ?>
		<b><?php echo $data['version']; ?> (<?php echo $data['went_live']; ?>):</b>

		<ul><?php
			foreach ($data['changes'] as $change) { ?>
				<li>
					<span style="font-size:125%;color:greenyellow;"><?php echo $change['title']; ?></span>
					<br /><?php echo $change['message']; ?><br /><br />
				</li><?php
			} ?>
		</ul>
		<br /><?php
	} ?>
</div>
