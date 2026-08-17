<?php declare(strict_types=1);

namespace Smr\Pages\Layout;

use Smr\Account;
use Smr\Globals;
use Smr\Pages\Account\AlbumEdit;
use Smr\Pages\Account\BugReport;
use Smr\Pages\Account\ChatJoin;
use Smr\Pages\Account\ContactForm;
use Smr\Pages\Account\Donation;
use Smr\Pages\Account\GameLeaveProcessor;
use Smr\Pages\Account\GamePlay;
use Smr\Pages\Account\HallOfFameAll;
use Smr\Pages\Account\LogoffProcessor;
use Smr\Pages\Account\Preferences;
use Smr\Pages\Admin\AdminTools;
use Smr\Pages\Player\CombatLogList;
use Smr\Pages\Player\ForcesList;
use Smr\Pages\Player\GalacticPost\CurrentEditionProcessor;
use Smr\Pages\Player\NewsReadCurrent;
use Smr\Pages\Player\Rankings\PlayerExperience;
use Smr\Pages\Player\SearchForTrader;
use Smr\Player;

class LeftPanelRenderer {

public static function render(?Account $ThisAccount, ?Player $ThisPlayer): void {
if ($ThisPlayer !== null) {
	$PlotCourseLink = Globals::getPlotCourseHREF();
	$TraderLink = Globals::getTraderStatusHREF();
	$PoliticsLink = Globals::getCouncilHREF($ThisPlayer->getRaceID());
	$CombatLogsLink = new CombatLogList()->href();
	$PlanetLink = Globals::getPlanetListHREF($ThisPlayer->getAllianceID());
	$ForcesLink = new ForcesList()->href();
	$MessagesLink = Globals::getViewMessageBoxesHREF();
	$ReadNewsLink = new NewsReadCurrent()->href();
	$GalacticPostLink = new CurrentEditionProcessor()->href();
	$SearchForTraderLink = new SearchForTrader()->href();
	$RankingsLink = new PlayerExperience()->href();
	$CurrentHallOfFameLink = new HallOfFameAll($ThisPlayer->getGameID())->href();
?>
<div id="LeftNavOne" class="leftNav noWrap"><?php
	// Use the current sector link for Planet Main to enable the hotkey
	if ($ThisPlayer->isLandedOnPlanet()) { ?>
		<a class="big bold" href="<?php echo Globals::getCurrentSectorHREF(); ?>">Planet Main</a><br /><?php
	} else { ?>
		<a class="big bold" href="<?php echo Globals::getCurrentSectorHREF(); ?>">Current Sector</a><br />
		<a class="big bold" href="<?php echo Globals::getLocalMapHREF(); ?>">Local Map</a><br /><?php
	} ?>
	<a class="big bold" href="<?php echo $PlotCourseLink; ?>">Plot A Course</a><br />
	<a href="map_galaxy.php" target="gal_map">Galaxy Map</a><br />
	<a href="<?php echo Globals::getSmrFileCreateHREF(); ?>" target="_blank">DL Sectors File</a><br />
	<br />
	<a href="<?php echo $TraderLink; ?>">Trader</a><br />
	<a href="<?php echo Globals::getAllianceHREF($ThisPlayer->getAllianceID()); ?>">Alliance</a><br />
	<a href="<?php echo $PoliticsLink; ?>">Politics</a><br />
	<a href="<?php echo $CombatLogsLink; ?>"><span>Combat Logs</span></a><br />
	<a href="<?php echo $PlanetLink; ?>">Planets</a><br />
	<a href="<?php echo $ForcesLink; ?>">Forces</a><br />
	<br />
	<a href="<?php echo $MessagesLink; ?>">Messages</a><br />
	<a href="<?php echo $ReadNewsLink; ?>">Read News</a><br />
	<a href="<?php echo $GalacticPostLink; ?>">Galactic Post</a><br />
	<a href="<?php echo Globals::getCasinoHREF(); ?>">Casino</a><br />
	<br />
	<a href="<?php echo $SearchForTraderLink; ?>">Search For Trader</a><br />
	<a href="<?php echo Globals::getCurrentPlayersHREF(); ?>">Current Players</a><br />
	<br />
	<a href="<?php echo $RankingsLink; ?>">Rankings</a><br />
	<a href="<?php echo $CurrentHallOfFameLink; ?>">Current HoF</a><br />
</div>
	<?php
}
?>
<div id="LeftNavTwo" class="leftNav nowrap">
<?php
if ($ThisAccount !== null) {
	$PlayGameLink = new GameLeaveProcessor(new GamePlay())->href();
	$PreferencesLink = new Preferences()->href();
	$LogoutLink = new LogoffProcessor()->href();
	$HallOfFameLink = new HallOfFameAll()->href();
	?>
	<a href="<?php echo $PlayGameLink; ?>">Play Game</a><br />
	<a href="<?php echo $PreferencesLink; ?>">Preferences</a><br /><?php
	if ($ThisAccount->hasPermission()) {
		$AdminToolsLink = new GameLeaveProcessor(new AdminTools())->href(); ?>
		<a href="<?php echo $AdminToolsLink; ?>">Admin Tools</a><br /><?php
	}
	?><a href="<?php echo $LogoutLink; ?>">Logout</a><br />
	<br />
	<a href="<?php echo $HallOfFameLink; ?>">Hall of Fame</a><br />
	<?php
} else {
	?><a href="login.php">Login</a><br /><?php
}
//<a href="http://www.azool.us/baalz/" target="manual">Help Pages</a><br />
$EditPhotoLink = new AlbumEdit()->href();
$ReportABugLink = new BugReport()->href();
$ContactFormLink = new ContactForm()->href();
$IRCLink = new ChatJoin()->href();
$DonateLink = new Donation()->href();
?>
<a href="<?php echo $EditPhotoLink; ?>">Edit Photo</a><br />
<a href="album/" target="album">View Album</a><br /><br /><?php
if (Globals::isFeatureRequestOpen()) {
	?><a href="<?php echo Globals::getFeatureRequestHREF(); ?>">Request A Feature</a><br /><?php
} ?>
<a href="<?php echo $ReportABugLink; ?>">Report A Bug</a><br />
<a href="<?php echo $ContactFormLink; ?>">Contact Form</a><br />
<br />
<a class="bold" href="<?php echo $IRCLink; ?>">Join Chat</a><br />
<a href="<?php echo WIKI_URL; ?>/rules" target="policy">User Policy</a><br />
<a href="<?php echo WIKI_URL; ?>" target="_blank">SMR Wiki</a><br />
<a href="http://smrcnn.smrealms.de/" target="webboard">Webboard</a><br />
<a href="<?php echo $DonateLink; ?>">Donate</a>
</div>
<?php
}

}
