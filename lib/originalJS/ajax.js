(function() {
	"use strict";
	var bind, updateRefreshTimeout, refreshSpeed, ajaxRunning = true, refreshReady = true, disableStartAJAX=false, xmlHttpRefresh, sn, updateRefresh, updateRefreshRequest, stopAJAX, getURLParameter;

	bind = function(func) {
		var args = Array.prototype.slice.call(arguments,1);
		return function() {
			return func.apply(this, args);
		};
	};
	
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

	window.onunload = window.stopAJAX = stopAJAX = function() {
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
	var highlighted = [], highlightMoves, submitMove;

	submitMove = function(data) {
		var e = $(this);
		data.toX = e.data('x');
		data.toY = e.data('y');
		$.get(submitMoveHREF, data, function(data, textStatus, jqXHR) {
				highlightMoves();
				updateRefresh(data, textStatus, jqXHR);
			}, 'xml');
	};
	
	window.highlightMoves = highlightMoves = function() {
		var e, x, y, boundSubmitMove, highlighted = $('.chessHighlight');
		if(highlighted.length === 0) {
			e = $(this);
			x = e.data('x');
			y = e.data('y');
			boundSubmitMove = bind(submitMove, {x:x,y:y});
			$(availableMoves[y][x]).addClass('chessHighlight').each(function(i, e) {
				e.onclick = boundSubmitMove;
			});
		}
		else {
			highlighted.removeClass('chessHighlight').each(function(i, e){
				e.onclick = highlightMoves;
			});
		}
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
	"use strict";
	$('.wep1:visible').slideToggle(600);
	$('.wep1:hidden').fadeToggle(600);
	$.get(link);
};

var linkFollowed = false;
window.followLink = function(href) {
	"use strict";
	return function() {
		if(linkFollowed !== true) {
			linkFollowed = true;
			window.location.href = href;
			window.stopAJAX();
		}
	};
};