(function() {
	var ajaxRunning = true, refreshReady = true, disableStartAJAX=false, xmlHttpRefresh, sn, updateRefresh, updateRefreshRequest, addOnClickToLinks, stopAJAX;

	// startAJAX
	window.onfocus = function()
	{
		// Start the Ajax updates
		if(!disableStartAJAX)
		{
			ajaxRunning = true;
			updateRefreshRequest();
		}
	}

	// pauseAJAX
	window.onblur = function()
	{
		// Pause the Ajax updates
		ajaxRunning = false;
	}

	window.onunload = stopAJAX = function()
	{
		// Stop the Ajax updates
		disableStartAJAX=true;
		ajaxRunning = false;
		if(xmlHttpRefresh!==null)
			xmlHttpRefresh.abort();
	}

	addOnClickToLinks = function()
	{
		var i,aLinks = document.getElementsByTagName('a');
		for( i = 0; i < aLinks.length; i++ )
		{
			aLinks[i].onmouseup = stopAJAX;
		}
		addOnClickToLinks = function(){};
	}

	getURLParameter = function(paramName)
	{
		var paramValue = false, href = window.location.href, paramDetail;
		if ( href.indexOf("?") > -1 )
		{
			var i, paramListStr = href.substr(href.indexOf("?")), paramList = paramListStr.split("&");
			for ( i = 0; i < paramList.length; i++ )
			{
				if (paramList[i].toUpperCase().indexOf(paramName.toUpperCase() + "=") > -1 )
				{
					paramDetail = paramList[i].split("=");
					paramValue = paramDetail[1];
					break;
				}
			}
		}
		return paramValue;
	}


	updateRefresh = function(data, textStatus, jqXHR)
	{
		var all, i, x, content;
		if (data && data.getElementsByTagName("all"))
		{
			all = data.getElementsByTagName("all")[0].childNodes;
			for(x=0;x<all.length;x++)
			{
				content='';
				for(i=0;i<all[x].childNodes.length;i++)
					content+=all[x].childNodes[i].nodeValue;
				document.getElementById(all[x].tagName).innerHTML=content;
			}
			if(ajaxRunning)
			{
				refreshReady = true;
				setTimeout(updateRefreshRequest, refreshSpeed);
			}
		}
	}

	updateRefreshRequest = function()
	{
		if(ajaxRunning && refreshReady)
		{
			refreshReady = false;
			xmlHttpRefresh = $.get('', {sn:sn, ajax:1}, updateRefresh, 'xml');
		}
	}

	window.startRefresh = function(_refreshSpeed)
	{
		if(!_refreshSpeed)
			return;
		refreshSpeed = _refreshSpeed;
		sn = getURLParameter('sn');
		if(sn===false)
			return;
		addOnClickToLinks();
		updateRefreshRequest();
	}



	/* weapon toggle */
	window.toggleWepD = function(link)
	{
		$('.wep1:visible').slideToggle(600);
		$('.wep1:hidden').fadeToggle(600);
		$.get(link);
	}
	
	window.followLink = function(href)
	{
		return function(){ window.location.href = href };
	}
})();