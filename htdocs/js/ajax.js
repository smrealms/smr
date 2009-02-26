var ajaxRunning = true;

window.onunload = stopAJAX;
document.onblur = pauseAJAX;
document.onfocus = startAJAX;

var disableStartAJAX=false;
function startAJAX()
{
	if(disableStartAJAX) return;
    // Start the Ajax updates
	ajaxRunning = true;
}

function pauseAJAX()
{
    // Pause the Ajax updates
	ajaxRunning = false;
}

function stopAJAX()
{
    // Stop the Ajax updates
	disableStartAJAX=true;
	ajaxRunning = false;
	if(xmlHttpRefresh!=null)
		xmlHttpRefresh.abort();
}

var onClickAdded = false;
function addOnClickToLinks()
{
	if(!onClickAdded)
	{
		onClickAdded = true;
		var aLinks = document.getElementsByTagName('a');
		for( var i = 0; i < aLinks.length; i++ )
		{
			aLinks[i].onmouseup = stopAJAX;
			// aLinks[i].onmousedown = stopAJAX;
		}
	}
}

/*ajax*/
function GetXmlHttpObject()
{
	addOnClickToLinks();
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

function getURLParameter(paramName)
{
	var paramValue = false;
	var href = window.location.href;
	if ( href.indexOf("?") > -1 )
	{
		var paramListStr = href.substr(href.indexOf("?"));
		var paramList = paramListStr.split("&");
		for ( var i = 0; i < paramList.length; i++ )
		{
			if (paramList[i].toUpperCase().indexOf(paramName.toUpperCase() + "=") > -1 )
			{
				var paramDetail = paramList[i].split("=");
				paramValue = paramDetail[1];
				break;
			}
		}
	}
	return paramValue;
}


//current page updating
var xmlHttpRefresh;
var alert_refresh = 0;
var last_refresh_comp = true;
var intervalRefresh;
var sn;
function startRefresh(refresh_speed)
{
	if(!refresh_speed)
		return;
	sn=getURLParameter('sn');
	if(sn===false)
		return;
	intervalRefresh = setInterval("updateRefresh()",refresh_speed);
}
function updateRefresh()
{
	if (!ajaxRunning || last_refresh_comp == false) return;
	last_refresh_comp = false;
	xmlHttpRefresh=GetXmlHttpObject();
	if (xmlHttpRefresh==null)
	{
		alert ('Browser does not support HTTP Request');
		return;
	}
	var url_a='loader.php?sn='+sn+'&ajax=1';
	xmlHttpRefresh.onreadystatechange=updateRefreshComp;
	xmlHttpRefresh.open("GET",url_a,true);
	xmlHttpRefresh.send(null);
}

function updateRefreshComp() 
{
	if (xmlHttpRefresh.readyState==4)
	{
		var xmlDoc=xmlHttpRefresh.responseXML;
		if(!xmlDoc || !xmlDoc.getElementsByTagName("time"))
		{
			clearInterval(intervalRefresh);
			return;
		}
		document.getElementById("tod").innerHTML=xmlDoc.getElementsByTagName("time")[0].childNodes[0].nodeValue;
		document.getElementById("runtime").innerHTML=xmlDoc.getElementsByTagName("runtime")[0].childNodes[0].nodeValue;
		var content='';
		if(xmlDoc.getElementsByTagName("htmlcontent").length>0)
		{
			for(var i=0;i<xmlDoc.getElementsByTagName("htmlcontent")[0].childNodes.length;i++)
				content+=xmlDoc.getElementsByTagName("htmlcontent")[0].childNodes[i].nodeValue;
			document.getElementById("middle_panel").innerHTML=content;
		}
		if(xmlDoc.getElementsByTagName("rightpanelhtml").length>0)
		{
			content='';
			for(var i=0;i<xmlDoc.getElementsByTagName("rightpanelhtml")[0].childNodes.length;i++)
				content+=xmlDoc.getElementsByTagName("rightpanelhtml")[0].childNodes[i].nodeValue;
			document.getElementById("right_panel").innerHTML=content;
		}
		last_refresh_comp = true;
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
var armour
var old_armour;
var max_armour;
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

function initRP(_shields,_oldShields,_maxShields,_armour,_oldArmour,_maxArmour,_cd,_oldCD,_maxCD,_maint,_maintColor)
{
	shields=_shields;
	old_shields=_oldShields;
	max_shields=_maxShields;
	
	armour=_armour;
	old_armour=_oldArmour;
	max_armour=_maxArmour;
	
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
	var rp_time = 5000;
	if (speed == 'fast') rp_time = 800;
//	intervalRP = setInterval("updateRP()",rp_time);
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
			document.getElementById("middle_panel").innerHTML='Session has expired. Please Relogin.';
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
		if(xmlDoc.getElementsByTagName("armour")[0])
		{
			armour = xmlDoc.getElementsByTagName("armour")[0].childNodes[0].nodeValue;
			old_armour = armour;
		}
//		old_armour = xmlDoc.getElementsByTagName("old_armour")[0].childNodes[0].nodeValue;
		if(xmlDoc.getElementsByTagName("max_armour")[0])
		{
			max_armour = xmlDoc.getElementsByTagName("max_armour")[0].childNodes[0].nodeValue;
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