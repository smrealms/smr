(function() {
	"use strict";
	var updateRefreshTimeout, refreshSpeed, ajaxRunning = true, refreshReady = true, disableStartAJAX=false, xmlHttpRefresh, sn, updateRefresh, updateRefreshRequest, stopAJAX, getURLParameter;

	// startAJAX
	window.onfocus = function() {
		// Start the Ajax updates
		if(!disableStartAJAX) {
			ajaxRunning = true;
			clearTimeout(updateRefreshTimeout);
			updateRefreshRequest();
		}
	};

	// pauseAJAX
	window.onblur = function() {
		// Pause the Ajax updates
		ajaxRunning = false;
	};

	window.onunload = stopAJAX = function() {
		// Stop the Ajax updates
		clearTimeout(updateRefreshTimeout);
		disableStartAJAX=true;
		ajaxRunning = false;
		if(xmlHttpRefresh!==null) {
			xmlHttpRefresh.abort();
		}
	};

	getURLParameter = function(paramName) {
		var paramValue = false, href = window.location.href, paramDetail;
		if ( href.indexOf("?") > -1 ) {
			var i, paramListStr = href.substr(href.indexOf("?")), paramList = paramListStr.split("&");
			for ( i = 0; i < paramList.length; i++ ) {
				if (paramList[i].toUpperCase().indexOf(paramName.toUpperCase() + "=") > -1 ) {
					paramDetail = paramList[i].split("=");
					paramValue = paramDetail[1];
					break;
				}
			}
		}
		return paramValue;
	};


	updateRefresh = function(data, textStatus, jqXHR) {
		var all = $('all > *', data).each(function(i, e) {
			if(e.tagName === 'JS') {
				$(e.childNodes).each(function(i, e) {
					if(e.tagName === 'ALERT') {
						$(JSON.parse($(e).text())).each(function(i, e) {
							alert(e);
						});
					}
					window[e.tagName] = JSON.parse($(e).text());
				});
			}
			else {
				$('#'+e.tagName).html($(e).text());
			}
		});
		if(all.length !== 0) {
			refreshReady = true;
			if(ajaxRunning === true) {
				clearTimeout(updateRefreshTimeout);
				updateRefreshTimeout = setTimeout(updateRefreshRequest, refreshSpeed);
			}
		}
	};

	updateRefreshRequest = function() {
		if(ajaxRunning === true && refreshReady === true) {
			refreshReady = false;
			xmlHttpRefresh = $.get('', {sn:sn, ajax:1}, updateRefresh, 'xml');
		}
	};
	
	
	//Chess
	/*global availableMoves:true, submitMoveHREF:true */
	var highlighted = [], highlightMoves, unhighlightMoves, submitMove;
	window.highlightMoves = highlightMoves = function(x,y) {
		var moves, i, ele, toX, toY,
			bindSubmitMove = (function(data) {
				return function() {
					submitMove(data);
				};
			});
		if(highlighted.length === 0) {
			moves = availableMoves['x'+x+'y'+y];
			if(moves != null) {
				for(i=0; i < moves.length; i++) {
					toX = moves[i].x;
					toY = moves[i].y;
					ele = $('#x'+toX+'y'+toY);
					highlighted.push({"ele":ele,"x":toX,"y":toY});
					ele[0].onclick = bindSubmitMove({x:x,y:y,toX:toX,toY:toY});
					ele.addClass('chessHighlight');
				}
			}
		}
		else {
			unhighlightMoves();
		}
	};
	unhighlightMoves = function() {
		var h,
			bindHiglightMoves = (function(x,y) {
				return function() {
					highlightMoves(x,y);
				};
			});
		while((h = highlighted.pop()) != null) {
			h.ele.removeClass('chessHighlight');
			h.ele[0].onclick = bindHiglightMoves(h.x,h.y);
		}
	};

	submitMove = function(data) {
		$.get(submitMoveHREF, data, function(data, textStatus, jqXHR) {
				unhighlightMoves();
				updateRefresh(data, textStatus, jqXHR);
			}, 'xml');
	};

	/*
	function sendMessage() {
		var msgBox = document.getElementById('messageBox');
		$.get('/Webtech/SendMessage', {msg: msgBox.value}, updateChatCallback, 'xml');
		msgBox.value = '';
	};

	function updateChat() {
		$.get('/Webtech/UpdateChat', updateChatCallback, 'xml');
	};

	function updateChatCallback(data) {
		document.getElementById('chat').innerHTML = getTextElem(data, 'CHAT');
	};
	*/
	

	//Globals
	window.startRefresh = function(_refreshSpeed) {
		if(!_refreshSpeed) {
			return;
		}
		refreshSpeed = _refreshSpeed;
		sn = getURLParameter('sn');
		if(sn===false) {
			return;
		}
		updateRefreshRequest();
	};
	
	$(function() {
		$('a').each(function(i, e) {
			e.onmouseup = function() {
				linkFollowed = true;
				stopAJAX();
			};
		});
	});
})();

//Standalone Globals
window.toggleWepD = function(link) {
	$('.wep1:visible').slideToggle(600);
	$('.wep1:hidden').fadeToggle(600);
	$.get(link);
};

var linkFollowed = false;
window.followLink = function(href) {
	return function() {
		if(linkFollowed !== true) {
			linkFollowed = true;
			window.location.href = href;
			stopAJAX();
		}
	};
};