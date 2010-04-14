var ajaxRunning = true,disableStartAJAX=false,onClickAdded = false;

//current page updating
var xmlHttpRefresh, last_refresh_comp = true, intervalRefresh, sn;

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
	if(xmlHttpRefresh!==null)
		xmlHttpRefresh.abort();
}

window.onunload = stopAJAX;
window.onblur = pauseAJAX;
window.onfocus = startAJAX;


function addOnClickToLinks()
{
	if(!onClickAdded)
	{
		onClickAdded = true;
		var i,aLinks = document.getElementsByTagName('a');
		for( i = 0; i < aLinks.length; i++ )
		{
			aLinks[i].onmouseup = stopAJAX;
			// aLinks[i].onmousedown = stopAJAX;
		}
	}
}

/*ajax*/
function getXmlHttpObject()
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
		catch (e2)
		{
			//IE 5.5
			xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
		}
	}
	return xmlHttp;
}

function getURLParameter(paramName)
{
	var paramValue = false,href = window.location.href;
	if ( href.indexOf("?") > -1 )
	{
		var i, paramListStr = href.substr(href.indexOf("?")), paramList = paramListStr.split("&");
		for ( i = 0; i < paramList.length; i++ )
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


function updateRefreshComp() 
{
	if (xmlHttpRefresh.readyState==4)
	{
		var xmlDoc=xmlHttpRefresh.responseXML,content,i,all,each,x;
		if(!xmlDoc || !xmlDoc.getElementsByTagName("all"))
		{
			clearInterval(intervalRefresh);
			return;
		}
		all = xmlDoc.getElementsByTagName("all")[0].childNodes;
		for(x=0;x<all.length;x++)
		{
			content='';
			for(i=0;i<all[x].childNodes.length;i++)
				content+=all[x].childNodes[i].nodeValue;
			document.getElementById(all[x].tagName).innerHTML=content;
		}
		last_refresh_comp = true;
	}
}
function updateRefresh()
{
	if (!ajaxRunning || last_refresh_comp === false) return;
	last_refresh_comp = false;
	if (xmlHttpRefresh===null)
	{
		alert ('Browser does not support HTTP Request');
		return;
	}
	var url_a='?sn='+sn+'&ajax=1';
	xmlHttpRefresh.open("GET",url_a,true);
	xmlHttpRefresh.onreadystatechange=updateRefreshComp; //Has to be after open to reuse in IE
	xmlHttpRefresh.send(null);
}

function startRefresh(refresh_speed)
{
	if(!refresh_speed)
		return;
	sn=getURLParameter('sn');
	if(sn===false)
		return;
	xmlHttpRefresh=getXmlHttpObject();
	intervalRefresh = setInterval(updateRefresh,refresh_speed);
}



/* weapon toggle */
function toggleWepD(amount,link)
{
	var xmlHttp,i;
	for(i = 1; i <= amount; i++)
	{
		if (document.getElementById('wep_item' + i).style.display == 'none')
			document.getElementById('wep_item' + i).style.display = 'block';
		else
			document.getElementById('wep_item' + i).style.display = 'none';
	}
	xmlHttp=getXmlHttpObject();
	xmlHttp.open("GET",link,true);
	xmlHttp.send(null);
}