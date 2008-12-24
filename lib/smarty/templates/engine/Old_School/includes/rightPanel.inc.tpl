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
{if isset($MessageGlobalLink) || isset($MessagePlayerLink) || isset($MessageScoutLink) || isset($MessagePoliticalLink) || isset($MessageAllianceLink) || isset($MessageAdminLink) || isset($MessagePlanetLink)}<br />{/if}
{$ThisPlayer->getLevelName()}<br />
<a href="{$PlayerNameLink}">{$ThisPlayer->getDisplayName()}</a><br />
{if isset($PlayerInvisible)}
	<span class="smallFont smallCaps red">INVISIBLE</span><br />
{/if}
<br />
Race : {$ThisPlayer->getRaceName()}<br />
{if $ThisPlayer->hasNewbieTurns()}
	Newbie Turns : <span class="{$NewbieTurnsColour}">{$ThisPlayer->getNewbieTurns()}</span><br />
{/if}
Credits : {$ThisPlayer->getCredits()}<br />
Experience : {$ThisPlayer->getExperience()}<br />
Level : {$ThisPlayer->getLevelID()}<br />
Next Level :<br />
<img src="images/bar_left.gif" title="0/83" alt="0/83" />
<img src="images/blue.gif" style="width:0px;height:10px;" title="0/83" alt="0/83" />
<img src="images/bar_border.gif" style="width:100px;height:10px;" title="0/83" alt="0/83" />
<img src="images/bar_right.gif" title="0/83" alt="0/83" /><br />
Alignment : {$ThisPlayer->getAlignment()}<br />
Alliance : {$ThisPlayer->getAllianceName()}{if $ThisPlayer->hasAlliance()} ({$ThisPlayer->getAllianceID()}){/if}<br />
<br />
<span id="ship_name"><a  href="{$ThisShip->getUpgradeShipHREF()}"><span class="yellow bold">{$ThisShip->getName()}</span></a></span><br />
{if $PlayerShipCustomName}{$PlayerShipCustomName}<br />{/if}
Rating : <span id="offense">{$ThisShip->getAttackRating()}</span>/<span id="defense">{$ThisShip->getDefenseRating()}</span><br />
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
Ship Condition : <span id="condition"><span class="dgreen">{$ThisPlayer->getTurns()}/{$ThisPlayer->getMaxTurns()}</span></span><br />
<br />
<a  href="{$ForceDropLink}"><span class="bold">Forces</span></a><br />
{if isset($DropMineLink)}<a href="{$DropMineLink}"><span class="bold">[X]</span></a>{/if}Mines : {$ThisShip->getMines()}/{$ThisShip->getMaxMines()}<br />
{if isset($DropCDLink)}<a href="{$DropCDLink}"><span class="bold">[X]</span></a>{/if}Combat : <span id="cds">{$ThisShip->getCDs()}/{$ThisShip->getMaxCDs()}</span><br />
{if isset($DropSDLink)}<a href="{$DropSDLink}"><span class="bold">[X]</span></a>{/if}Scout : {$ThisShip->getSDs()}/{$ThisShip->getMaxSDs()}<br /><br />
<a  href="{$CargoJettisonLink}"><span class="bold">Cargo Holds ({$ThisShip->getCargoHolds()}/{$ThisShip->getMaxCargoHolds()})</span></a><br />
Empty : {$ThisShip->getEmptyHolds()}<br /><br />
<a  href="{$WeaponReorderLink}"><span class="bold">Weapons</span></a><br />
<div class="wep_drop1" onclick="toggleWepD({$ShipWeapons|@count});">Show/Hide
	{foreach from=$ShipWeapons item=WeaponName name=WeaponLoop}
		<div class="wep1" id="wep_item{$smarty.foreach.WeaponLoop.iteration}">{$WeaponName}</div>
	{/foreach}
</div>
Open : {$ThisShip->getOpenWeaponSlots()}<br />Total Damage: ({$ThisShip->getTotalShieldDamage()}/{$ThisShip->getTotalArmourDamage()})<br />
Power Used: Not Done Yet<br /><br />
<a  href="main.php?action=53"><span class="bold">Gadget Slot</span></a><br />
<span id="gadgets">Empty<br /></span><br />
<br />
<a  href="main.php?action=78"><span class="bold">Track Damages</span></a><br />
<br />
<a href="{$Globals->getBetaFunctionsHREF()}"><span class="bold">Beta Functions</span></a>
<script type="text/javascript">initRP(450,150,450,175,175,450,0,0,15,100,'dgreen');startRP('slow');</script>