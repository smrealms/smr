{if $Ticker}
	<div style="overflow:auto;height:8em;border:2px solid #0b8d45;text-align:left">
		{foreach from=$Ticker item=Tick}
			{$Tick.Time}: &nbsp; {$Tick.Message}<br /><br />
		{foreachelse}
			Nothing to report
		{/foreach}
	</div><br />
{/if}