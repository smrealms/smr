<h1>{$Topic}</h1><br />
<div class="bar1">
	<div>
		<span class="nowrap"><a href="{$PlotCourseLink}">Plot a Course</a></span> | <span class="nowrap"><a href="{$LocalMapLink}">Local Map</a></span> | <span class="nowrap"><a href="map_galaxy.php" target="_blank">Galaxy Map</a></span>
	</div>
</div><br />
<table cellspacing="0" cellpadding="0" style="width:100%;border:none">
	<tr>
		<td style="padding:0px;vertical-align:top">
			{include_template template="includes/SectorNavigation.inc" assign=Template}{include file=$Template}
		</td>
		<td style="padding:0px;vertical-align:top;width:32em;">
			{include_template template="includes/PlottedCourse.inc" assign=Template}{include file=$Template}
			{include_template template="includes/Ticker.inc" assign=Template}{include file=$Template}
			
			{if $ErrorMessage}{$ErrorMessage}<br />{/if}
			{if $ProtectionMessage}{$ProtectionMessage}<br />{/if}
			{if $TurnsMessage}{$TurnsMessage}<br />{/if}
			{if $TradeMessage}{$TradeMessage}<br />{/if}
			{if $ForceRefreshMessage}{$ForceRefreshMessage}<br />{/if}
			{if $VarMessage}{$VarMessage}<br />{/if}
			
			
			{*
				{if $}{$}<br />{/if}
			*}
		</td>
	</tr>
</table><br />
{include_template template="includes/SectorPlanet.inc" assign=Template}{include file=$Template}
{include_template template="includes/SectorPort.inc" assign=Template}{include file=$Template}
{include_template template="includes/SectorLocations.inc" assign=Template}{include file=$Template}
{include_template template="includes/SectorPlayers.inc" assign=Template}{include file=$Template}
{include_template template="includes/SectorForces.inc" assign=Template}{include file=$Template}
<br />