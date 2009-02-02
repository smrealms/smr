{$TargetPort->getDisplayName()}'s defenses are <span class="red">DESTROYED!</span><br />
{if $KillResults}
	{$ShootingPlayer->getDisplayName()} claims <span class="yellow">{$KillResults.KillerCredits|number_format}</span> credits from the port.<br />
{/if}