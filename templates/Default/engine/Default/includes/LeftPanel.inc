<span class="yellow">
	<span id="tod"><?php echo $timeDisplay; ?></span>
</span><br />
<br /><?php
if(isset($GameID)) {
	?><big><?php
	if(!$ThisPlayer->isLandedOnPlanet()) {
		?><a class="bold" href="<?php echo Globals::getCurrentSectorHREF(); ?>">Current Sector</a><br /><?php
	 }
	if(isset($PlanetMainLink)) {
		?><a class="bold" href="<?php echo $PlanetMainLink; ?>">Planet Main</a><br /><?php
	 }
	if(isset($LocalMapLink)) {
		?><a class="bold" href="<?php echo Globals::getLocalMapHREF(); ?>">Local Map</a><br /><?php
	}
	if(isset($PlotCourseLink)) {
		?><a class="bold" href="<?php echo $PlotCourseLink; ?>">Plot A Course</a><br /><?php
	}
	?></big>
	<a href="map_galaxy.php" target="gal_map">Galaxy Map</a><br />
	<br />
	<a href="<?php echo $TraderLink; ?>">Trader</a><br />
	<a href="<?php echo Globals::getAllianceHREF($ThisPlayer->getAllianceID()); ?>">Alliance</a><br />
	<a href="<?php echo $PoliticsLink; ?>">Politics</a><br />
	<a href="<?php echo $CombatLogsLink; ?>"><span>Combat Logs</span></a>
	<br />
	<br />
	<?php /* <a href="<?php echo $TradingLink; ?>">Trading</a><br /> */ ?>
	<a href="<?php echo $PlanetLink; ?>">Planet</a><br />
	<a href="<?php echo $ForcesLink; ?>">Forces</a><br />
	<a href="<?php echo Globals::getCasinoHREF(); ?>">Casino</a><br />
	<br />
	<a href="<?php echo $MessagesLink; ?>">Messages</a><br />
	<a href="<?php echo $ReadNewsLink; ?>">Read News</a><br />
	<a href="<?php echo $GalacticPostLink; ?>">Galactic Post</a><br />
	<br />
	<a href="<?php echo $SearchForTraderLink; ?>">Search For Trader</a><br />
	<a href="<?php echo Globals::getCurrentPlayersHREF(); ?>">Current Players</a><br />
	<br />
	<a href="<?php echo $RankingsLink; ?>">Rankings</a><br />
	<a href="<?php echo $HallOfFameLink; ?>">Hall of Fame</a><br />
	<a href="<?php echo $CurrentHallOfFameLink; ?>">Current HoF</a><br />
	<br /><?php
}
if(isset($AccountID)) { ?>
	<a href="<?php echo $PlayGameLink; ?>">Play Game</a><br />
	<a href="<?php echo $PreferencesLink; ?>">Preferences</a><br /><?php
	if($ThisAccount->hasPermission()) { ?>
		<a href="<?php echo $AdminToolsLink; ?>">Admin Tools</a><br /><?php
	}
	?><a href="<?php echo $LogoutLink; ?>">Logout</a><br />
	<br /><?php
}
else {
	?><a href="login.php">Login</a><br /><?php
}
//<a href="http://www.azool.us/baalz/" target="manual">Help Pages</a><br />
?>
<a href="http://wiki.smrealms.de" target="_blank">SMR Wiki</a><br /><?php
if(isset($GameID)) {
	?><a href="<?php echo Globals::getSmrFileCreateHREF(); ?>" target="_self">DL Sectors File</a><br /><?php
} ?>
<a href="<?php echo $EditPhotoLink; ?>">Edit Photo</a><br />
<a href="album/" target="album">View Album</a><br /><br /><?php
if(Globals::isFeatureRequestOpen()) {
	?><a href="<?php echo Globals::getFeatureRequestHREF(); ?>">Request A Feature</a><br /><?php
}
if(isset($GameID) && Globals::isBetaOpen()) {
	?><a href="<?php echo Globals::getBetaApplyHREF(); ?>">Apply For Beta</a><br /><?php
} ?>
<a href="<?php echo $ReportABugLink; ?>">Report A Bug</a><br />
<a href="<?php echo $ContactFormLink; ?>">Contact Form</a><br />
<br /><?php
if(isset($GameID)) {
	?><a class="bold" href="<?php echo $IRCLink; ?>">IRC Chat</a><br /><?php
} ?>
<a class="bold" href="http://smrcnn.smrealms.de/viewtopic.php?f=1&amp;t=9705" target="policy">User Policy</a><br />
<a class="bold" href="http://smrcnn.smrealms.de/" target="webboard">Webboard</a><br />
<a href="<?php echo $DonateLink; ?>">Donate</a><br />
<br />