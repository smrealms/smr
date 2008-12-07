//smr.js
function VoteSite(url,sn) {
	window.open(url);
	str = 'loader.php?sn=' + sn; 
	window.location=str;
}


//smr15.js
/*
Wep Drag Adapted for SMR use by Azool.
Original Source from http://www.cyberdummy.co.uk/test/dd.php
*/

var ajaxRunning = true;

window.onblur = function()
{
    // Pause the Ajax updates
    ajaxRunning = false;
};
window.onfocus = function()
{
    // Start the Ajax updates
    ajaxRunning = true;
};

function stopAJAX()
{
	ajaxRunning = false;
}

//var onClickAdded = false;
//function addOnClickToLinks()
//{
//	if(!onClickAdded)
//	{
//		onClickAdded = true;
//		var aLinks = document.getElementsByTagName( 'a' );
//		for( var i = 0; i < aLinks.length; i++ )
//		{
//			aLinks[i].onclick = stopAJAX;
//		}
//	}
//}

/*ajax*/
function GetXmlHttpObject()
{
//	addOnClickToLinks();
	var xmlHttp=null;
	try
	{
		// Firefox, Opera 8.0+, Safari
		xmlHttp=new XMLHttpRequest();
	}
	catch (e)
	{
		// Internet Explorer
		try
		{
			xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
		}
		catch (e)
		{
			//IE 5.5
			xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
		}
	}
	return xmlHttp;
}

//current sector updating

var xmlHttpCS;
var alert_cs = 0;
var last_cs_comp = 1;
var intervalCS;
function startCS(cs_type)
{
	if (cs_type == 'part') { var r_time = 60000; }
	else if (cs_type == 'fed') { var r_time = 5000; }
	else { var r_time = 800; }
	intervalCS = setInterval("updateCS('"+cs_type+"')",r_time);
}
function updateCS(cs_type)
{
	if (!ajaxRunning || last_cs_comp == 0) return;
	last_cs_comp = 0;
	xmlHttpCS=GetXmlHttpObject();
	if (xmlHttpCS==null)
	{
		alert ("Browser does not support HTTP Request");
		return;
	}
	if (cs_type == 'full' || cs_type == 'fed') { var url_a="processAJAX.php?s=updateCS.php&t=full"; }
	else { var url_a="processAJAX.php?s=updateCS.php&t=part"; }
	url_a=url_a+"&sid="+Math.random();
	xmlHttpCS.onreadystatechange=updateCSComp;
	xmlHttpCS.open("GET",url_a,true);
	xmlHttpCS.send(null);
}

function updateCSComp() 
{
	if (xmlHttpCS.readyState==4)
	{
		xmlDocCS=xmlHttpCS.responseXML;
		var cs_p_string = '';
		var force_string = '';
		var cs_refreshable = 0;
		
		var runtime = xmlDocCS.getElementsByTagName("runtime")[0].childNodes[0].nodeValue;
		document.getElementById("runtime").innerHTML=runtime;
		
		//use this for when session is killed
		var kill = xmlDocCS.getElementsByTagName("kill")[0].childNodes[0].nodeValue;
		if (kill == 'TRUE')
		{
			document.getElementById("middle_panel").innerHTML='Session has expired.  Please Relogin.';
			clearInterval(intervalCS);
			return;
		}
		
		var new_q = xmlDocCS.getElementsByTagName("new")[0].childNodes[0].nodeValue;
		if (new_q == 'FALSE') { last_cs_comp = 1; return; }
		
		var cloaked = xmlDocCS.getElementsByTagName("cloaked")[0].childNodes[0].nodeValue;
		
		var players = xmlDocCS.getElementsByTagName("current_player");
		var forces = xmlDocCS.getElementsByTagName("forces");
		
		var all_id = xmlDocCS.getElementsByTagName("all_id")[0].childNodes[0].nodeValue;
		var err_msg = xmlDocCS.getElementsByTagName("message")[0].childNodes[0].nodeValue;
		err_msg = err_msg.replace(/\[/g,"<");
		err_msg = err_msg.replace(/\]/g,">");
		if (document.getElementById("error_message"))
		{
			if (err_msg != 'FALSE') { document.getElementById("error_message").innerHTML=err_msg; }
			else { document.getElementById("error_message").innerHTML=''; }
		}
		
		//var active = xmlDocCS.getElementsByTagName("active")[0].childNodes[0].nodeValue;
		var time = xmlDocCS.getElementsByTagName("time")[0].childNodes[0].nodeValue;

		if (players.length > 0)
		{
			cs_p_string = cs_p_string + '<table class="standard fullwidth" cellspacing="0">';
			cs_p_string = cs_p_string + '<tr><th colspan="5" style="background:#550000;">Ships (' + players.length + ')</th></tr>';
			cs_p_string = cs_p_string + '<tr><th>Trader</th><th>Ship</th><th>Rating</th><th>Level</th><th >Option</th></tr>';
			for (i=0;i<players.length;i=i+1) {
				var level = players[i].getElementsByTagName("level")[0].childNodes[0].nodeValue;
				var p_type = players[i].getElementsByTagName("type")[0].childNodes[0].nodeValue;
				var p_name = players[i].getElementsByTagName("name")[0].childNodes[0].nodeValue;
				var p_id = players[i].getElementsByTagName("player_id")[0].childNodes[0].nodeValue;
				var color = players[i].getElementsByTagName("color")[0].childNodes[0].nodeValue;
				var ship = players[i].getElementsByTagName("ship")[0].childNodes[0].nodeValue;
				var off = players[i].getElementsByTagName("offense")[0].childNodes[0].nodeValue;
				var def = players[i].getElementsByTagName("defense")[0].childNodes[0].nodeValue;
				var alliance = players[i].getElementsByTagName("alliance")[0].childNodes[0].nodeValue;
				var action_id = players[i].getElementsByTagName("action_id")[0].childNodes[0].nodeValue;
				var alliance_action_id = players[i].getElementsByTagName("alliance_action_id")[0].childNodes[0].nodeValue;
				cs_p_string = cs_p_string + '<tr><td><span style="color:#' + color + '">' + p_name;
				if (p_id > 0) { cs_p_string = cs_p_string + ' (' + p_id + ')'; }
				cs_p_string = cs_p_string + '</span>';
				if (p_type == 'NPC') { cs_p_string = cs_p_string + ' <span class="npcColor">[NPC]</span>'; }
				cs_p_string = cs_p_string + ' (';
				if (alliance_action_id > 0) cs_p_string = cs_p_string + '<a href="main.php?action=' + alliance_action_id + '">';
				cs_p_string = cs_p_string + alliance;
				if (alliance_action_id > 0) cs_p_string = cs_p_string + '</a>';
				cs_p_string = cs_p_string + ')</td>';
				cs_p_string = cs_p_string + '<td>' + ship + '</td><td class="shrink center nowrap">' + off + '/' + def + '</td>';
				cs_p_string = cs_p_string + '<td class="shrink center nowrap">' + level + '</td>';
				cs_p_string = cs_p_string + '<td class="shrink center nowrap"><div class="buttonA"><a class="buttonA" href="main.php?action=' + action_id + '">&nbsp; Examine &nbsp;</a></div></td></tr>';
			}
			cs_p_string = cs_p_string + '</table><br />';
		} else if (cloaked == 'TRUE') { cs_p_string = '<span class="red">NOTICE</span> : Cloaked Vessel Detected'; }
		else { cs_p_string = ''; }
		if (document.getElementById("players_cs")) { document.getElementById("players_cs").innerHTML= cs_p_string; }
		
		//forces
		if (forces.length > 0)
		{
			force_string = force_string + '<table class="standard fullwidth" cellspacing="0">';
			force_string = force_string + '<tr><th colspan="6" style="background:#000055;color:#80C870">Forces (' + forces.length + ')</th></tr>';
			force_string = force_string + '<tr><th>Mines</th><th>Combat</th><th>Scout</th><th>Expires</th><th>Owner</th><th>Option</th></tr>';
			for (i=0;i<forces.length;i=i+1) {
				//player info
				var p_name = forces[i].getElementsByTagName("name")[0].childNodes[0].nodeValue;
				var p_type = forces[i].getElementsByTagName("type")[0].childNodes[0].nodeValue;
				var p_id = forces[i].getElementsByTagName("player_id")[0].childNodes[0].nodeValue;
				var color = forces[i].getElementsByTagName("color")[0].childNodes[0].nodeValue;
				var alliance = forces[i].getElementsByTagName("alliance")[0].childNodes[0].nodeValue;
				var alliance_action_id = forces[i].getElementsByTagName("alliance_action_id")[0].childNodes[0].nodeValue;
				
				//force info
				var mines = forces[i].getElementsByTagName("mines")[0].childNodes[0].nodeValue;
				var combat_drones = forces[i].getElementsByTagName("combat_drones")[0].childNodes[0].nodeValue;
				var scout_drones = forces[i].getElementsByTagName("scout_drones")[0].childNodes[0].nodeValue;
				var expire = forces[i].getElementsByTagName("expire")[0].childNodes[0].nodeValue;
				var examine_id = forces[i].getElementsByTagName("examine_id")[0].childNodes[0].nodeValue;
				var refresh_id = forces[i].getElementsByTagName("refresh_id")[0].childNodes[0].nodeValue;
				if (refresh_id > 0) { cs_refreshable = 1; }
				
				//add this info to the string
				force_string = force_string + '<tr><td class="center shrink nowrap">'+mines+'</td>';
				force_string = force_string + '<td class="center shrink nowrap">'+combat_drones+'</td>';
				force_string = force_string + '<td class="center shrink nowrap">'+scout_drones+'</td>';
				force_string = force_string + '<td class="shrink nowrap center">';
				if (refresh_id > 0) { force_string = force_string + '<span class="green">'+expire; }
				else { force_string = force_string + '<span class="red bold">Unknown'; }
				force_string = force_string + '</span></td>';
				force_string = force_string + '<td><span style="color:#'+color+'">'+p_name;
				if (p_id > 0) { force_string = force_string + ' ('+p_id+')'; }
				force_string = force_string + '</span>';
				if (p_type == 'NPC') { force_string = force_string + ' <span class="npcColor">[NPC]</span>'; }
				force_string = force_string + '<br />(';
				//test to make line move
				//more test
				var blah = 0;
				if (blah > 0) { alert('1'); }
				if (alliance_action_id > 0) { force_string = force_string + '<a href="main.php?action=' + alliance_action_id + '">'; }
				force_string = force_string + alliance;
if (alliance_action_id > 0) { force_string = force_string + '</a>'; }
				force_string = force_string + ')</td>';
				force_string = force_string + '<td align="center" class="shrink center"><div class="buttonA"><a class="buttonA" href="main.php?action='+examine_id+'">&nbsp;Examine&nbsp;</a></div>';
				if (refresh_id > 0) { force_string = force_string + '<br /><br /><div class="buttonA"><a class="buttonA" href="main.php?action='+refresh_id+'">&nbsp;Refresh&nbsp;</a></div>'; }
				force_string = force_string + '</td></tr>';
			}
			if (cs_refreshable == 1) { force_string = force_string + '<tr><td colspan="6" class="center"><div class="buttonA"><a class="buttonA" href="main.php?action='+all_id+'">&nbsp;Refresh All&nbsp;</a></div></td></tr>'; }
			force_string = force_string + '</table><br />';
		} else { force_string = ''; }
		if (document.getElementById("sector_forces")) { document.getElementById("sector_forces").innerHTML= force_string; }
		
		last_cs_comp = 1;
	}
}

//Right panel info
var xmlHttp;
var last_comp = 1;
var flashing = 0;
var shi_hit = 0;
var cd_hit = 0;
var arm_hit = 0;
var testing = 0;
var began = 0;
var intervalRP;

var shields;
var old_shields;
var max_shields;
var armor
var old_armor;
var max_armor;
var cd;
var old_cd;
var max_cd;

var attack;
var time;
var maint;
var maint_color;
var def;
var off;
var ship_name;
var ship_action;
var dead;
var gadget;
var gad_str = '';

function initRP(_shields,_oldShields,_maxShields,_armor,_oldArmor,_maxArmor,_cd,_oldCD,_maxCD,_maint,_maintColor)
{
	shields=_shields;
	old_shields=_oldShields;
	max_shields=_maxShields;
	
	armor=_armor;
	old_armor=_oldArmor;
	max_armor=_maxArmor;
	
	cd=_cd;
	old_cd=_oldCD;
	max_cd=_maxCD;
	
	maint=_maint;
	maint_color=_maintColor;
}

function startRP(speed)
{
	if (began == 1) return;
	began = 1;
	if (speed == 'fast') { var rp_time = 800; }
	else { var rp_time = 5000; }
	intervalRP = setInterval("updateRP()",rp_time);
}
function updateRP()
{
	if (!ajaxRunning || last_comp == 0) return;
	last_comp = 0;
	xmlHttp=GetXmlHttpObject();
	if (xmlHttp==null)
	{
		alert ("Browser does not support HTTP Request");
		return;
	}
	var url_a="processAJAX.php?s=updateRP.php";
	url_a=url_a+"&sid="+Math.random();
	xmlHttp.onreadystatechange=updateRPComp ;
	xmlHttp.open("GET",url_a,true);
	xmlHttp.send(null);
}



function updateRPComp() 
{
	if (xmlHttp.readyState==4 || xmlHttp.readyState=="complete")
	{
		xmlDoc=xmlHttp.responseXML;

		var runtime = xmlDoc.getElementsByTagName("runtime")[0].childNodes[0].nodeValue;
		//document.getElementById("runtime").innerHTML=runtime;
		
		//use this for when session is killed
		var kill = xmlDoc.getElementsByTagName("kill")[0].childNodes[0].nodeValue;
		if (kill == 'TRUE')
		{
			document.getElementById("middle_panel").innerHTML='Session has expired.  Please Relogin.';
			clearInterval(intervalRP); // Stop updating when timed out.
			return;
		}
		
		var tod1 = xmlDoc.getElementsByTagName("tod1")[0].childNodes[0].nodeValue;
		var tod2 = xmlDoc.getElementsByTagName("tod2")[0].childNodes[0].nodeValue;
		document.getElementById("tod").innerHTML=tod1 + '<br />' + tod2;
		
		//see if we have new data
		var new_q = xmlDoc.getElementsByTagName("new")[0].childNodes[0].nodeValue;
		if (new_q == 'FALSE') { last_comp = 1; return; }
		
		var msgs = xmlDoc.getElementsByTagName("message_t")[0].childNodes[0].nodeValue;
		msgs = msgs.replace(/\[/g,"<");
		msgs = msgs.replace(/\]/g,">");
		if (msgs != 'None') { document.getElementById("message_area").innerHTML=msgs; }
		
		//if (testing == 0) { testing = 1; alert('1'); }
		if(xmlDoc.getElementsByTagName("shields")[0])
		{
			old_shields = shields;
			shields = xmlDoc.getElementsByTagName("shields")[0].childNodes[0].nodeValue;
		}
//		old_shields = xmlDoc.getElementsByTagName("old_shields")[0].childNodes[0].nodeValue;
		if(xmlDoc.getElementsByTagName("max_shields")[0])
		{
			max_shields = xmlDoc.getElementsByTagName("max_shields")[0].childNodes[0].nodeValue;
		}
		if(xmlDoc.getElementsByTagName("armor")[0])
		{
			armor = xmlDoc.getElementsByTagName("armor")[0].childNodes[0].nodeValue;
			old_armor = armor;
		}
//		old_armor = xmlDoc.getElementsByTagName("old_armor")[0].childNodes[0].nodeValue;
		if(xmlDoc.getElementsByTagName("max_armor")[0])
		{
			max_armor = xmlDoc.getElementsByTagName("max_armor")[0].childNodes[0].nodeValue;
		}
		if(xmlDoc.getElementsByTagName("combat_drones")[0])
		{
			cd = xmlDoc.getElementsByTagName("combat_drones")[0].childNodes[0].nodeValue;
			old_cd = cd;
		}
//		old_cd = xmlDoc.getElementsByTagName("old_combat_drones")[0].childNodes[0].nodeValue;
		if(xmlDoc.getElementsByTagName("max_cds")[0])
		{
			max_cd = xmlDoc.getElementsByTagName("max_cds")[0].childNodes[0].nodeValue;
		}
		//if (testing == 1) { testing = 2; alert('2'); }
		attack = xmlDoc.getElementsByTagName("attack")[0].childNodes[0].nodeValue;
		time = xmlDoc.getElementsByTagName("time")[0].childNodes[0].nodeValue;
		//if (testing == 2) { testing = 3; alert('3'); }
		if(xmlDoc.getElementsByTagName("maint")[0])
		{
			maint = xmlDoc.getElementsByTagName("maint")[0].childNodes[0].nodeValue;
			maint_color = xmlDoc.getElementsByTagName("maint_color")[0].childNodes[0].nodeValue;
		}
		def = xmlDoc.getElementsByTagName("defense")[0].childNodes[0].nodeValue;
		off = xmlDoc.getElementsByTagName("offense")[0].childNodes[0].nodeValue;
		ship_name = xmlDoc.getElementsByTagName("ship_name")[0].childNodes[0].nodeValue;
		ship_action = xmlDoc.getElementsByTagName("ship_name_action")[0].childNodes[0].nodeValue;
		dead = xmlDoc.getElementsByTagName("dead")[0].childNodes[0].nodeValue;
		//if (testing == 3) { testing = 4; alert('4'); }
		gadget = xmlDoc.getElementsByTagName("gadget");
		//if (testing == 4) { testing = 5; alert('5'); }
		gad_str = '';
		if (gadget.length > 0)
		{
			for (i=0;i<gadget.length;i=i+1) {
				var gad_id = gadget[i].getElementsByTagName("gad_id")[0].childNodes[0].nodeValue;
				if (gad_id > 0) {
					var gad_name = gadget[i].getElementsByTagName("gad_name")[0].childNodes[0].nodeValue;
					var cooldown = gadget[i].getElementsByTagName("cooldown")[0].childNodes[0].nodeValue;
					var equipped = gadget[i].getElementsByTagName("equipped")[0].childNodes[0].nodeValue;
					var time_left = gadget[i].getElementsByTagName("time_left")[0].childNodes[0].nodeValue;
					var action_id = gadget[i].getElementsByTagName("action_id")[0].childNodes[0].nodeValue;
					if (action_id > 0) {
						gad_str = gad_str + '<a href="main.php?action=' + action_id + '">' + gad_name + '</a><br />';
					} else {
						gad_str = gad_str + gad_name + '<br />';
					}
					if (cooldown > time) { gad_str = gad_str + 'Cooldown: ' + time_left + '<br />'; }
				} else { gad_str = 'Empty<br />'; }
			}
		} else { gad_str = 'Empty<br />'; }
		var ship_str = '<a href="main.php?action=' + ship_action + '"><span class="yellow bold">' + ship_name + '</span></a>';
		//if (testing == 5) { testing = 6; alert('6'); }
		var dead_str = '<p>As the hull of your ship collapses, you quickly launch out in your escape pod. Activating the emergency warp system, your stomach turns as you are hurled through hyperspace back to a safe destination.</p><p><img src="images/escape_pod.jpg"></p>';
		if (dead == 'TRUE') document.getElementById("middle_panel").innerHTML=dead_str;
		document.getElementById("gadgets").innerHTML=gad_str;
		document.getElementById("ship_name").innerHTML=ship_str;
		document.getElementById("defense").innerHTML=def;
		document.getElementById("offense").innerHTML=off;
		document.getElementById("condition").innerHTML='<span class="' + maint_color + '">' + maint + '/100</span>';
		
		
		if (attack != "FALSE")
		{
			TriggerAttackBlink(attack);
		}
		last_comp = 1;
	}
}

var currentlyFlashing=false;
function TriggerAttackBlink(colour)
{
	if (old_shields != shields) shi_hit = 1;
	if (old_cd != cd) cd_hit = 1;
	if (old_armor != armor) arm_hit = 1;
	
	if (shi_hit == 1)
		document.getElementById("shields").innerHTML='<span class="red">' + shields + '/' + max_shields + '</span>';
	if (cd_hit == 1)
		document.getElementById("cds").innerHTML='<span class="red">' + cd + '/' + max_cd + '</span>';
	if (arm_hit == 1)
		document.getElementById("armor").innerHTML='<span class="red">' + armor + '/' + max_armor + '</span>';
		
	document.getElementById("attack_area").innerHTML= '<div class="attack_warning">You Are Under Attack!</div>';
	color = xmlDoc.getElementsByTagName("attack")[0].childNodes[0].nodeValue;
	if (currentlyFlashing == false)
	{
		currentlyFlashing = true;
		//flash 3 times
		setTimeout('ajax_flash1(\'' + colour + '\')',0);
		setTimeout('ajax_flash1(\'' + colour + '\')',2000);
		setTimeout('ajax_flash1(\'' + colour + '\')',4000);

		setTimeout('ajax_flash2()',1000);
		setTimeout('ajax_flash2()',3000);
		setTimeout('ajax_flash2()',5000);
		setTimeout('stopFlash()',5000);
	}
}

function stopFlash()
{
	currentlyFlashing = false;
}

function ajax_flash1(color)
{
	document.bgColor="#" + color;
}

function ajax_flash2()
{
	document.bgColor="#0B2121";
}

function nothing() {}

function startCalc(){
	interval = setInterval("calc()",1);
}
function calc(){
	one = document.form.port1.value;
	two = document.form.port2.value;
	three = document.form.port3.value;
	four = document.form.port4.value;
	five = document.form.port5.value;
	six = document.form.port6.value;
	seven = document.form.port7.value;
	eight = document.form.port8.value;
	nine = document.form.port9.value;
	document.form.total.value = (one * 1) + (two * 1) + (three * 1) + (four * 1) + (five * 1) + (six * 1) + (seven * 1) + (eight * 1) + (nine * 1);
}
function stopCalc(){
	clearInterval(interval);
}
function startCalcM(){
	intervalM = setInterval("calcM()",1);
}
function calcM(){
	one = document.form.mine1.value;
	two = document.form.mine2.value;
	three = document.form.mine3.value;
	four = document.form.mine4.value;
	five = document.form.mine5.value;
	six = document.form.mine6.value;
	seven = document.form.mine7.value;
	eight = document.form.mine8.value;
	nine = document.form.mine9.value;
	ten = document.form.mine10.value;
	ele = document.form.mine11.value;
	twe = document.form.mine12.value;
	thir = document.form.mine13.value;
	fourt = document.form.mine14.value;
	fift = document.form.mine15.value;
	sixt = document.form.mine16.value;
	sevent = document.form.mine17.value;
	eighte = document.form.mine18.value;
	ninete = document.form.mine19.value;
	twent = document.form.mine20.value;
	document.form.totalM.value = (one * 1) + (two * 1) + (three * 1) + (four * 1) + (five * 1) + (six * 1) + (seven * 1) + (eight * 1) + (nine * 1) + (ten * 1) + (ele * 1) + (twe * 1) + (thir * 1) + (fourt * 1) + (fift * 1) + (sixt * 1) + (sevent * 1) + (eighte * 1) + (ninete * 1) + (twent * 1);
}
function stopCalcM(){
	clearInterval(intervalM);
}
function set_even(){
	document.form.race0.value = 12;
	document.form.race10.value = 11;
	document.form.race20.value = 11;
	document.form.race30.value = 11;
	document.form.race40.value = 11;
	document.form.race50.value = 11;
	document.form.race60.value = 11;
	document.form.race70.value = 11;
	document.form.race80.value = 11;
	document.form.racedist.value = 100;
}
function startRaceCalc(){
	intervalRace = setInterval("Racecalc()",1);
}
function Racecalc(){
	one = document.form.race0.value;
	two = document.form.race10.value;
	three = document.form.race20.value;
	four = document.form.race30.value;
	five = document.form.race40.value;
	six = document.form.race50.value;
	seven = document.form.race60.value;
	eight = document.form.race70.value;
	nine = document.form.race80.value;
	document.form.racedist.value = (one * 1) + (two * 1) + (three * 1) + (four * 1) + (five * 1) + (six * 1) + (seven * 1) + (eight * 1) + (nine * 1);
}
function stopRaceCalc(){
	clearInterval(intervalRace);
}

/* weapon toggle */
function toggleWepD(amount)
{
	for(var i = 1; i <= amount; i++)
	{
		if (document.getElementById('wep_item' + i).style.display == 'block')
			document.getElementById('wep_item' + i).style.display = 'none';
		else
			document.getElementById('wep_item' + i).style.display = 'block';
	}
}

//startRP();
//startCS();