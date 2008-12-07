<div align="center">
	Local Map of the Known <span class="big bold">
	{$GalaxyName}
	</span> Galaxy.<br />
	{$error}
	<br />
	<a id="status" onClick="toggleM();">Mouse Zoom is {if $isZoomOn}On{else}Off{/if}.  Click to toggle.</a>
</div><br />

{include_template template="includes/SectorMap.inc" assign=Template}{include file=$Template}