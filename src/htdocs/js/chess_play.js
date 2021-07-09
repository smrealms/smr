"use strict";

(function() {

	/** global availableMoves, submitMoveHREF */

	function submitMove(data) {
		var e = $(this);
		data.toX = e.data('x');
		data.toY = e.data('y');
		ajaxLink(submitMoveHREF, highlightMoves, data);
	}

	function bindOne(func, arg) {
		return function() {
			return func.call(this, arg);
		};
	}

	window.highlightMoves = function() {
		var highlighted = $('.chessHighlight');
		if (highlighted.length === 0) {
			var e = $(this);
			var x = e.data('x');
			var y = e.data('y');
			var boundSubmitMove = bindOne(submitMove, {x:x,y:y});
			$(availableMoves[y][x]).addClass('chessHighlight').each(function(i, e) {
				e.onclick = boundSubmitMove;
			});
		} else {
			highlighted.removeClass('chessHighlight').each(function(i, e){
				e.onclick = highlightMoves;
			});
		}
	};

})();
