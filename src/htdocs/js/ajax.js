"use strict";

// We declare and use this function to make clear to the minifier that the eval will not effect the later parts of the script despite being called there.
var exec = function(s) {
	eval(s);
};

(function() {
	var updateRefreshTimeout, refreshSpeed, refreshReady = true, xmlHttpRefresh, refreshUrl = null;

	/**
	 * Turn off AJAX auto-refresh by default. It can only be enabled by
	 * a call to `setupRefresh`.
	 */
	var refreshEnabled = false;

	/**
	 * Schedule the next AJAX auto-refresh.
	 */
	function scheduleRefresh() {
		// Remove any existing refresh schedule
		clearTimeout(updateRefreshTimeout);
		// Delay before first AJAX udpate to avoid rapid re-triggers.
		updateRefreshTimeout = setTimeout(updateRefreshRequest, refreshSpeed);
	}

	/**
	 * Stops AJAX auto-refresh and cancels the current request.
	 * Can be restarted with `scheduleRefresh()`.
	 */
	function cancelRefresh() {
		clearTimeout(updateRefreshTimeout);
		if (xmlHttpRefresh !== undefined) {
			xmlHttpRefresh.abort();
		}
	}

	/**
	 * Disables AJAX auto-refresh permanently on this page.
	 */
	function disableRefresh() {
		refreshEnabled = false;
		cancelRefresh();
	}

	// This is used as a jQuery.get callback, but we don't use the arguments
	// (textStatus, jqXHR), so they are omitted here.
	function updateRefresh(data) {
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
		scheduleRefresh();
	}

	function updateRefreshRequest() {
		if (refreshEnabled === true && refreshReady === true) {
			refreshReady = false;
			xmlHttpRefresh = $.get(refreshUrl, {ajax:1}, updateRefresh, 'xml');
		}
	}

	/**
	 * Set up the AJAX auto-refresh for this page.
	 * Specify refreshSpeed in milliseconds.
	 */
	window.initRefresh = function(_refreshSpeed) {
		// If auto-refresh is disabled in preferences, then this function is
		// not called, so make sure the refresh is enabled ONLY if this
		// function is called.
		refreshEnabled = true;

		// Similarly, we only need event listeners when refresh is enabled.
		window.onfocus = scheduleRefresh;
		window.onblur = cancelRefresh;
		window.onunload = cancelRefresh;

		if(!_refreshSpeed) {
			return;
		}
		refreshSpeed = _refreshSpeed;
		refreshUrl = location.href;
		scheduleRefresh();
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
				disableRefresh();
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
				disableRefresh();
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
				disableRefresh();
			}
			e.preventDefault();
		});
	});

	/**
	 * Perform a generic AJAX update.
	 * Auto-refresh is stopped during the request, and started back up
	 * (if not disabled) after the request completes.
	 */
	window.ajaxLink = function(link, callback=null, params={}) {
		cancelRefresh();
		params.ajax = 1;
		$.get(link, params, function(data) {
				refreshUrl = link;
				updateRefresh(data);
				if (callback !== null) {
					callback();
				}
			}, 'xml');
	};

	window.toggleWepD = function(link) {
		$('.wep1:visible').slideToggle(600);
		$('.wep1:hidden').fadeToggle(600);
		// Omit updateRefresh here so that we can smoothly animate the change
		$.get(link, {ajax: 1});
	};

})();
