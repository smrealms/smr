{$TargetPlayer->getDisplayName()} has been <span class="red">DESTROYED!</span><br />
{if $KillResults}
	{$ShootingPlayer->getDisplayName()} salvages <span class="yellow">{$KillResults.KillerCredits|number_format}</span> credits from the wreckage and gains <span class="blue">{$KillResults.KillerExp|number_format}</span> experience.<br />
{/if}