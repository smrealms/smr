<?php

$template->assign('PageTopic','Space Merchant Realms Chat Room Rules');

//$PHP_OUTPUT.=('<div align=center><br /><br /><br />VJ Has temporarly taken down the applet.  Please visit <a href="http://www.vjtd3.com/irc"><b>VJTD3's chat page</b></a> for a link to many applets.</div>');
//include('http://irc.VJTD3.com/index.shtml?' . strtr($player->getPlayerName(), array(' ' => '+')));
$PHP_OUTPUT .= "
<script language='JavaScript'>function setjs() {if(navigator.product == 'Gecko') {document.loginform['interface'].value = 'mozilla';}else if(navigator.appName == 'Microsoft Internet Explorer' &&window['ietest'] && window['ietest'].innerHTML) {document.loginform['interface'].value = 'ie';}else if(window.opera) {document.loginform['interface'].value = 'opera';}}</script>
These rules have been created for all chatters in #SMR. The purpose of #SMR is to have a general gathering of all players, newbies and vets, and anyone else who may decide to enter the channel. The channel is meant to help anyone with any questions or problems they may have regarding the game. The following is a list of the rules we ask that everyone follow:<br /><br />
<b>News Last Updated: <u>Sunday, 09-Feb-2003 19:45:15 EST</u></b><br />
MrSpock setup so the applet can be administered, no longer having to ban the applet for a single abusive user ;)<br />
<br /><br />
<b>Rules Last Updated: <u>Sunday, 21-Dec-2003 01:47:22 EST</u></b><br />
<br /><br />
A. Rules <br /><br />

<ol>
<li>No foul language is allowed. That includes swearing, explicit language, derogatory racial comments, and similar activities. </li>

<li>No advertising of sites not related to SMR is allowed. Limited advertisement of online game directories, news publications, and similar sites is accepted.</li>

<li>No promotion of illegal activities, including but not limited to illegal drug use, is allowed.</li>

<li>No cheating accusations are allowed. That includes, but is not limited to, accusations of having multiple accounts and abusing bugs. The proper action in those cases is an email to multi@smrealms.de in the former one and admin@smrealms.de (or bugs@smrealms.de) in the later.</li>

<li>No harassment of admins, ops, or regular players is allowed. That includes slandering, name-calling, and similar activites. That also includes actions that have as their sole purpose to annoy admins, ops, or other players.</li>

<li>Impersonating other players, admins, or ops, past and present, is strictly forbidden. For the purposes of this rule Space Merchant and Space Merchant Realms are considered to be the same game.</li>

<li>No flooding or spamming is allowed.</li>

<li>Since the only language that all of the ops speak is English, it is the only language allowed in #smr. If you want to speak other languages feel free to do it in other rooms or in PMs.</li>

<li>Ban hopping (logging in from another IP after you get banned and similar activities) is striclty forbidden.</li>

<li>Bots are allowed only with Spock's permission.</li>

<li>Complaints about kicks/bans are to be made in private(PM, email). Complaints in chat are not allowed.
</li>
</ol>

<br /><br />
B. Disciplinary actions.
<br /><br />

<ol>
	<li>There are 4 kinds of disciplinary action: a verbal warning, a kick, a short term ban (normally 24 hours), and a long term ban (up to life time).</li>
	
	<li>Disciplinary actions should start with the lowest level and continue to the highest.</li>
	
	<li>Normally, only the first 3 levels will be used with only 1 stop at each of the 2 lower levels.</li>
	
	<li>The amount of stops at lower levels can be increased if:
		<ol type='a'>
			<li>the offender is a newer chat user,</li>
			<li>the offence is very mild (for example usage of a language other than English), or</li>
			<li>other special circumstances are at hand.</li>
		</ol>
	</li>
	
	<li>The amount of stops at lower levels can be decreased if:
		<ol type='a'>
			<li>the offender has been punished multiple times,</li>
			<li>the offence is especially severe (for example spamming or impersonation of an old and well known member of community (for example Speef or MrSpock)), or</li>
			<li>other special circumstances are at hand.</li>
		</ol>
	</li>
	
	<li>In extreme cases of severe and/or multiple offences long term bans will be issued.</li>
	
	<li>In especially severe cases, including, but not limited to ban hopping in game action will be taken.</li>
	
	<li>If you disagree with the action taken against you feel free to complain to the chat admin aka Blum in chat or through email chat@smrealms.de</li>
</ol>
<br />
<br />
<br />";
$allianceChan = '';
if($player->hasAlliance())
{
	$allianceChan = $player->getAlliance()->getIrcChannel();
	if($allianceChan != '')
	{
		$allianceChan = ',' . urlencode($allianceChan);
	}
}
$PHP_OUTPUT .= '<center><a href="http://widget.mibbit.com/?settings=5f6a385735f22a3138c5cc6059dab2f4&server=irc.coldfront.net&channel=%23SMR'.$allianceChan.'&autoConnect=true&nick='.urlencode('SMR-'.str_replace(' ','_',$player->getPlayerName())).'" target="_chat" class="submitStyle">Chat</a></center>';
//$PHP_OUTPUT .= '<center><form method="POST" action="http://chat.vjtd3.com/sjc.php?Nickname='. urlencode('SMR-'.strtr($player->getPlayerName(), array(' ' => '_'))) .'&Channel=%23smr" name="loginform" onsubmit="setjs();return true;" id="ietest" target="_chat">

//<input type="hidden" name="interface" value="nonjs">';
 
//$PHP_OUTPUT.=('<input type="hidden" name="Nickname" value="' . strtr($player->getPlayerName(), array(' ' => '_')) . '">');
//<input type='hidden' name='Channel' value='#SMR'>
//$PHP_OUTPUT .= "<input type='submit' name='action' value='Chat' id='InputFields'></form></center>
//<br /><font size='2'><i>This Page Was Last Updated: Tuesday, 30-Mar-2004 21:41:44 EST</i></font>";

?>