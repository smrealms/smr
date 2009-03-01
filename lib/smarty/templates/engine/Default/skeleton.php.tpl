{include_template template="includes/header.inc" assign=Template}{include file=$Template}
<table class="m" align="center" cellspacing="0" cellpadding="0">
	<tr>
		<td class="l0" rowspan="2">
			<div class="l1">
				<div class="l2">
					{include_template template="includes/leftPanel.inc" assign=Template}{include file=$Template}
				</div>
			</div>
		</td>
		<td class="m0" colspan="2">
			<div class="m1">
				<div class="m2">
					<div id="middle_panel">
						{if $PageTopic}<h1>{$PageTopic}</h1><br />{/if}
						{if $MenuBar}{$MenuBar}{/if}
						{include_template template=$TemplateBody assign=Template}{include file=$Template}
					</div>
				</div>
			</div>
		</td>
		<td class="r0">
			<div class="r1">
				<div class="r2" id="right_panel">
					{include_template template="includes/rightPanel.inc" assign=Template}{include file=$Template}
				</div>
			</div>
		</td>
	</tr>
	<tr>
		<td class="footer_left">
			<div style="width:294px;text-align:center">Get <b><u>FREE TURNS</u></b> for voting if you see the star.</div>
			{foreach from=$VoteSites item=VoteSite}
				{$VoteSite}
			{/foreach}
		</td>
		<td class="footer_right">
			{include_template template="includes/copyright.inc" assign=Template}{include file=$Template}
		</td>
	</tr>
</table>
{include_template template="includes/footer.inc" assign=Template}{include file=$Template}