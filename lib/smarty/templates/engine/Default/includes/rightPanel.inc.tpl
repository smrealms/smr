<span id="attack_area">{if $ThisShip->removeUnderAttack()}<div class="attack_warning">You Are Under Attack!</div>{/if}</span>
<span id="message_area">
	{if isset($MessageGlobalLink)}
		<a  href="{$MessageGlobalLink}"><img src="images/global_msg.gif" alt="Global Messages" /></a>
		<span class="small">{$MessageGlobalNum}</span>
	{/if}
	{if isset($MessagePersonalLink)}
	<a  href="{$MessagePersonalLink}"><img src="images/personal_msg.gif" alt="Personal Messages" /></a>
	<span class="small">{$MessagePersonalNum}</span>
	{/if}
	{if isset($MessageScoutLink)}
	<a  href="{$MessageScoutLink}"><img src="images/scout_msg.gif" alt="Scout Messages" /></a>
	<span class="small">{$MessageScoutNum}</span>
	{/if}
	{if isset($MessagePoliticalLink)}
	<a  href="{$MessagePoliticalLink}"><img src="images/council_msg.gif" alt="Political Messages" /></a>
	<span class="small">{$MessagePoliticalNum}</span>
	{/if}
	{if isset($MessageAllianceLink)}
	<a  href="{$MessageAllianceLink}"><img src="images/alliance_msg.gif" alt="Alliance Messages" /></a>
	<span class="small">{$MessageAllianceNum}</span>
	{/if}
	{if isset($MessageAdminLink)}
	<a  href="{$MessageAdminLink}"><img src="images/admin_msg.gif" alt="Admin Messages" /></a>
	<span class="small">{$MessageAdminNum}</span>
	{/if}
	{if isset($MessagePlanetLink)}
	<a  href="{$MessagePlanetLink}"><img src="images/planet_msg.gif" alt="Planet Messages" /></a>
	<span class="small">{$MessagePlanetNum}</span>
	{/if}
</span>
{if isset($MessageGlobalLink) || isset($MessagePersonalLink) || isset($MessageScoutLink) || isset($MessagePoliticalLink) || isset($MessageAllianceLink) || isset($MessageAdminLink) || isset($MessagePlanetLink)}<br />{/if}
{$ThisPlayer->getLevelName()}<br />
<a href="{$PlayerNameLink}">{$ThisPlayer->getDisplayName()}</a><br />
{if isset($PlayerInvisible)}
	<span class="smallFont smallCaps red">INVISIBLE</span><br />
{/if}
<br />
Race : {$ThisPlayer->getRaceName()}<br />
Turns : <span id="condition"><span class="{if $ThisPlayer->getTurnsLevel() == 'LOW'}red{elseif $ThisPlayer->getTurnsLevel() == 'MEDIUM'}yellow{else}green{/if}">{$ThisPlayer->getTurns()}/{$ThisPlayer->getMaxTurns()}</span></span><br />
{if $ThisPlayer->hasNewbieTurns()}
	Newbie Turns : <span class="{$NewbieTurnsColour}">{$ThisPlayer->getNewbieTurns()}</span><br />
{/if}
Credits : {$ThisPlayer->getCredits()|number_format}<br />
Experience : {$ThisPlayer->getExperience()|number_format}<br />
Level : {$ThisPlayer->getLevelID()}<br />
Next Level :<br />
<img src="images/bar_left.gif" title="{$ThisPlayer->getExperience()|number_format}/{$ThisPlayer->getNextLevelExperience()|number_format}" alt="{$ThisPlayer->getExperience()|number_format}/{$ThisPlayer->getNextLevelExperience()|number_format}" />
<img src="images/blue.gif" style="width:{$ThisPlayer->getNextLevelPercentAcquired()}px;height:10px;" title="{$ThisPlayer->getExperience()|number_format}/{$ThisPlayer->getNextLevelExperience()|number_format}" alt="{$ThisPlayer->getExperience()|number_format}/{$ThisPlayer->getNextLevelExperience()|number_format}" />
<img src="images/bar_border.gif" style="width:{$ThisPlayer->getNextLevelPercentRemaining()}px;height:10px;" title="{$ThisPlayer->getExperience()|number_format}/{$ThisPlayer->getNextLevelExperience()|number_format}" alt="{$ThisPlayer->getExperience()|number_format}/{$ThisPlayer->getNextLevelExperience()|number_format}" />
<img src="images/bar_right.gif" title="{$ThisPlayer->getExperience()|number_format}/{$ThisPlayer->getNextLevelExperience()|number_format}" alt="{$ThisPlayer->getExperience()|number_format}/{$ThisPlayer->getNextLevelExperience()|number_format}" /><br />
Alignment : {$ThisPlayer->getAlignment()|number_format}<br />
Alliance : {$ThisPlayer->getAllianceName()}{if $ThisPlayer->hasAlliance()} ({$ThisPlayer->getAllianceID()|number_format}){/if}<br />
<br />
<span id="ship_name"><a  href="{$ThisShip->getUpgradeShipHREF()}"><span class="yellow bold">{$ThisShip->getName()}</span></a></span><br />
{if $PlayerShipCustomName}{$PlayerShipCustomName}<br />{/if}
Rating : <span id="offense">{$ThisShip->getAttackRating()|number_format}</span>/<span id="defense">{$ThisShip->getDefenseRating()|number_format}</span><br />
Shields : <span id="shields">{$ThisShip->getShields()}/{$ThisShip->getMaxShields()}</span><br />
Armor : <span id="armor">{$ThisShip->getArmour()}/{$ThisShip->getMaxArmour()}</span><br />
<a href="{$HardwareLink}">CIJSD</a> : {if $ThisShip->hasCloak()}*{else}-{/if}{if $ThisShip->hasIllusion()}*{else}-{/if}{if $ThisShip->hasJump()}*{else}-{/if}{if $ThisShip->hasScanner()}*{else}-{/if}{if $ThisShip->hasDCS()}*{else}-{/if}<br />
{if $ThisShip->hasCloak()}
	{if $ThisShip->isCloaked()}
		<strong style="color:lime;">*** Cloak active ***</strong>
	{else}
		<strong style="color:red;">*** Cloak inactive ***</strong>
	{/if}
	<br /><br />
{/if}
{if $ThisShip->hasActiveIllusion()}
	<span class="npcColor">{$ThisShip->getIllusionShipName()}</span><br />
	IG Rating : {$ThisShip->getIllusionAttack()}/{$ThisShip->getIllusionDefense()}<br />
	<br />
{/if}
<br />
<a href="{$ForceDropLink}"><span class="bold">Forces</span></a><br />
{if isset($DropMineLink)}<a href="{$DropMineLink}"><span class="bold">[X]</span></a>{/if}Mines : {$ThisShip->getMines()}/{$ThisShip->getMaxMines()}<br />
{if isset($DropCDLink)}<a href="{$DropCDLink}"><span class="bold">[X]</span></a>{/if}Combat : <span id="cds">{$ThisShip->getCDs()}/{$ThisShip->getMaxCDs()}</span><br />
{if isset($DropSDLink)}<a href="{$DropSDLink}"><span class="bold">[X]</span></a>{/if}Scout : {$ThisShip->getSDs()}/{$ThisShip->getMaxSDs()}<br /><br />
<a href="{$CargoJettisonLink}"><span class="bold">Cargo Holds ({$ThisShip->getCargoHolds()}/{$ThisShip->getMaxCargoHolds()})</span></a><br />
{if $ThisShip->hasCargo()}
	{foreach from=$ThisShip->getCargo() key=GoodID item=GoodAmount}
		{assign var=Good value=$Globals->getGood($GoodID)}
		<img src="{$Good.ImageLink}" alt="{$Good.Name}"> : {$GoodAmount}<br />
	{/foreach}
{/if}
Empty : {$ThisShip->getEmptyHolds()}<br /><br />
<a href="{$WeaponReorderLink}"><span class="bold">Weapons</span></a><br />
{if $ThisShip->hasWeapons()}
	<div class="wep_drop1" onclick="toggleWepD({$ThisShip->getNumWeapons()});">Show/Hide
		{foreach from=$ThisShip->getWeapons() item=Weapon name=WeaponLoop}
			<div class="wep1" {if !$ThisPlayer->isDisplayWeapons()}style="display: none;"{/if} id="wep_item{$smarty.foreach.WeaponLoop.iteration}">{$Weapon->getName()}</div>
		{/foreach}
	</div>
{/if}
Open : {$ThisShip->getOpenWeaponSlots()}<br />Total Damage: ({$ThisShip->getTotalShieldDamage()}/{$ThisShip->getTotalArmourDamage()})<br />
Power Used: {$ThisShip->getPowerUsed()}/{$ThisShip->getMaxPower()}<br /><br />
<a  href="main.php?action=53"><span class="bold">Gadget Slot</span></a><br />
<span id="gadgets">Empty<br /></span><br />
<br />
<a  href="main.php?action=78"><span class="bold">Track Damages</span></a><br />
<br />
<a href="{$Globals->getBetaFunctionsHREF()}"><span class="bold">Beta Functions</span></a>
<script type="text/javascript">initRP(450,150,450,175,175,450,0,0,15,100,'dgreen');startRP('slow');</script>