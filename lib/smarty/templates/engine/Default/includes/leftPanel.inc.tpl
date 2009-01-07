<span class="yellow">
	<span id="tod">{$timeDisplay}</span>
</span><br />
<br />
{if isset($GameID)}
	{if !$ThisPlayer->isLandedOnPlanet()}
		<a class="bold" href="{$ThisSector->getCurrentSectorHREF()}">Current Sector</a><br />
	{/if}
	{if isset($PlanetMainLink)}
		<a class="bold" href="{$PlanetMainLink}">Planet Main</a><br />
	{/if}
	{if isset($LocalMapLink)}
		<a class="bold" href="{$LocalMapLink}">Local Map</a><br />
	{/if}
	{if isset($PlotCourseLink)}
		<a class="bold" href="{$PlotCourseLink}">Plot A Course</a><br />
	{/if}
	<a href="map_galaxy.php" target="gal_map">Galaxy Map</a><br />
	<br />
	<a href="{$TraderLink}">Trader</a><br />
	<a href="{$AllianceLink}">Alliance</a><br />
	<a href="{$CombatLogsLink}"><span>Combat Logs</span></a>
	<br />
	<br />
	<a href="{$TradingLink}">Trading</a><br />
	<a href="{$PlanetLink}">Planet</a><br />
	<a href="{$ForcesLink}">Forces</a><br />
	<br />
	<a href="{$MessagesLink}">Messages</a><br />
	<a href="{$ReadNewsLink}">Read News</a><br />
	<a href="{$GalacticPostLink}">Galactic Post</a><br />
	<br />
	<a href="{$SearchForTraderLink}">Search For Trader</a><br />
	<a href="{$CurrentPlayersLink}">Current Players</a><br />
	<br />
	<a href="{$RankingsLink}">Rankings</a><br />
	<a href="{$HallOfFameLink}">Hall of Fame</a><br />
	<a href="{$CurrentHallOfFameLink}">Current HoF</a><br />
	<br />
{/if}
{if isset($AccountID)}
	<a href="{$PlayGameLink}">Play Game</a><br />
	<a  href="{$LogoutLink}">Logout</a><br />
	<br />
{else}
	<a href="login.php">Login</a><br />
{/if}
<a href="http://www.azool.us/baalz/" target="manual">Help Pages</a><br />
<a href="manual.php">Manual</a><br />
<a href="{$PreferencesLink}">Preferences</a><br />
{if isset($GameID)}
	<a href="smr_file_download.php">Download Sectors File</a><br />
{/if}
<a href="{$EditPhotoLink}">Edit Photo</a><br />
<a href="album/">Album</a><br /><br />
<a href="{$ReportABugLink}">Report A Bug</a><br />
<a href="{$ContactFormLink}">Contact Form</a><br />
<br />
{if isset($GameID)}
	<a class="bold" href="{$IRCLink}">IRC Chat</a><br />
{/if}
<a class="bold" href="http://smrcnn.smrealms.de/viewtopic.php?f=1&amp;t=9705" target="policy">User Policy</a><br />
<a class="bold" href="http://smrcnn.smrealms.de/" target="webboard">Webboard</a><br />
<a href="{$DonateLink}">Donate</a><br />
<br />