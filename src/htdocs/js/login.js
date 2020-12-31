/**
 * Inject CSS into the Twitter Feed iframe once it has loaded
 * to change the styling of the iframe scrollbar.
 */
function tryInject() {
	var widget = $("iframe#twitter-widget-0");
	if (widget.length == 0) {
		// If the iframe hasn't loaded yet, rerun this function again before
		// the next repaint.
		window.requestAnimationFrame(tryInject);
	} else {
		// Inject scrollbar CSS into the iframe (webkit only).
		widget.contents().find('head').append('<style>::-webkit-scrollbar { width: 10px; } ::-webkit-scrollbar-track { -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.8); border-radius: 10px; } ::-webkit-scrollbar-thumb { border-radius: 10px; -webkit-box-shadow: inset 0 0 6px rgba(128,200,112,1); }</style>');
		// Unhide the twitter feed element.
		$("td#twitter-feed").removeClass("hide");
	}
}

tryInject();
