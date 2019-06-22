// We declare and use this function to make clear to the minifier that the eval will not effect the later parts of the script despite being called there.
var exec = function(s) {
	eval(s);
};
(function() {
	"use strict";
	var bindOne, updateRefreshTimeout, refreshSpeed, ajaxRunning = true, refreshReady = true, disableStartAJAX=true, xmlHttpRefresh, sn, updateRefresh, updateRefreshRequest, stopAJAX;

	bindOne = function(func, arg) {
		return function() {
			return func.call(this, arg);
		};
	};

	// startAJAX
	window.onfocus = function() {
		// Start the Ajax updates if startRefresh has been called
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
		if (xmlHttpRefresh !== undefined) {
			xmlHttpRefresh.abort();
		}
	};

	function getURLParameter(paramName, href) {
		if ( href.indexOf("?") > -1 ) {
			var paramList = href.substr(href.indexOf("?")).split("&");
			for (var i = 0; i < paramList.length; i++) {
				if (paramList[i].toUpperCase().indexOf(paramName.toUpperCase() + "=") > -1 ) {
					return paramList[i].split("=")[1];
				}
			}
		}
		return false;
	}

	// This is used as a jQuery.get callback, but we don't use the arguments
	// (textStatus, jqXHR), so they are omitted here.
	updateRefresh = function(data) {
		$('all > *', data).each(function(i, e) {
			if(e.tagName === 'JS') {
				$(e.childNodes).each(function(i, e) {
					if(e.tagName === 'EVAL') {
						exec($(e).text());
					}
					else {
						if(e.tagName === 'ALERT') {
							$(JSON.parse($(e).text())).each(function(i, e) {
								alert(e);
							});
						}
						window[e.tagName] = JSON.parse($(e).text());
					}
				});
			}
			else {
				$('#'+e.tagName).html($(e).text());
			}
		});
		refreshReady = true;
		if(ajaxRunning === true) {
			clearTimeout(updateRefreshTimeout);
			updateRefreshTimeout = setTimeout(updateRefreshRequest, refreshSpeed);
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
	var highlightMoves, submitMove;

	submitMove = function(data) {
		var e = $(this);
		data.toX = e.data('x');
		data.toY = e.data('y');
		$.get(submitMoveHREF, data, function(data) {
				sn = getURLParameter('sn', submitMoveHREF);
				highlightMoves();
				updateRefresh(data);
			}, 'xml');
	};

	window.highlightMoves = highlightMoves = function() {
		var e, x, y, boundSubmitMove, highlighted = $('.chessHighlight');
		if(highlighted.length === 0) {
			e = $(this);
			x = e.data('x');
			y = e.data('y');
			boundSubmitMove = bindOne(submitMove, {x:x,y:y});
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


	//Globals
	window.startRefresh = function(_refreshSpeed) {
		// If auto-refresh is disabled in preferences, then this function is not called,
		// so make sure the refresh is enabled ONLY if this function is called.
		disableStartAJAX = false;

		if(!_refreshSpeed) {
			return;
		}
		refreshSpeed = _refreshSpeed;
		sn = getURLParameter('sn', location.href);
		if(sn===false) {
			return;
		}
		updateRefreshRequest();
	};

	// The following section attempts to prevent users from taking multiple
	// actions within the same page. Possible actions include:
	//
	//  1. Pressing a hotkey
	//  2. Submitting a form
	//  3. Clicking a link
	//
	// We need to ensure that doing any one of these actions prevents all
	// other actions from having any effect. The next three functions
	// attempt to accomplish this.
	//
	var linkFollowed = false;
	window.followLink = function(href) {
		"use strict";
		// Prevent further actions after a hotkey is pressed.
		return function() {
			if(linkFollowed !== true) {
				linkFollowed = true;
				location.href = href;
				stopAJAX();
			}
		};
	};
	$(function() {
		// Prevent further actions after a form is submitted.
		$('form').submit(function(e) {
			if (linkFollowed === true) {
				e.preventDefault();
			} else {
				linkFollowed = true;
				stopAJAX();
			}
		});
		// Prevent further actions after a link is clicked.
		// This is skipped if the link has a "target" attribute specified.
		$('a[href]:not([target])').click(function(e) {
			// Did we click the link with the left mouse button?
			// We don't want to trigger this on right/middle clicks.
			if(e.which !== 1) {
				return;
			}
			// Don't trigger if clicked link has a no-op href attribute.
			if (this.href === 'javascript:void(0)') {
				return;
			}
			if(linkFollowed !== true) {
				linkFollowed = true;
				location.href = this.href;
				stopAJAX();
			}
			e.preventDefault();
		});
	});

	window.toggleWepD = function(link) {
		"use strict";
		$('.wep1:visible').slideToggle(600);
		$('.wep1:hidden').fadeToggle(600);
		$.get(link, {ajax: 1});
	};

	window.toggleScoutGroup = function(senderID) {
		$('#group'+senderID).toggle();
	};
})();
