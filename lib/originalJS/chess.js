(function() {
	var highlighted = [], unhighlightMoves, getTextElem, submitMove;
	window.highlightMoves = function(x,y)
	{
		var moves, i, ele;
		if(highlighted.length==0)
		{
			moves = availableMoves['x'+x+'y'+y];
			if(moves != null)
			{
				for(i=0; i < moves.length; i++)
				{
					toX = moves[i].x;
					toY = moves[i].y;
					ele = $('#x'+toX+'y'+toY);
					highlighted.push({"ele":ele,"x":toX,"y":toY});
					ele.click((function(x,y,toX,toY){return function(){submitMove(x,y,toX,toY);}})(x,y,toX,toY));
					ele.addClass('chessHighlight');
				}
			}
		}
		else
		{
			unhighlightMoves();
		}
	}
	unhighlightMoves = function() {
		var h;
		while(h = highlighted.pop()) {
			h.ele.removeClass('chessHighlight');
			h.ele.click((function(x,y){return function(){highlightMoves(x,y)}})(h.x,h.y));
		}
	}

	getTextElem = document.getElementsByTagName("body")[0].textContent == undefined ?
		function(ele, name)
		{
			return ele.getElementsByTagName(name)[0].text;
		}
		:
		function(ele, name)
		{
			return ele.getElementsByTagName(name)[0].textContent;
		}

	submitMove = function(x,y,toX,toY)
	{
		$.get(submitMoveHREF, {x:x,y:y,toX:toX,toY:toY}, updateGameCallback, 'xml');
	}

	function updateGameCallback(data)
	{
		var x, y, i, j, moves;
		unhighlightMoves();
		var tiles = data.getElementsByTagName('TILE');
		for(i=0;i<tiles.length;i++)
		{
			x = getTextElem(tiles[i],'X');
			y = getTextElem(tiles[i],'Y');
			$('#x'+x+'y'+y).html(getTextElem(tiles[i],'INNER_HTML'));
			availableMoves['x'+x+'y'+y] = [];
			moves = tiles[i].getElementsByTagName('POSSIBLE_MOVE');
			for(j=0;j<moves.length;j++)
			{
				availableMoves['x'+x+'y'+y].push({'x':getTextElem(moves[j],'MOVE_X'),'y':getTextElem(moves[j],'MOVE_Y')});
			}
		}
		$('#moveTable').html(getTextElem(data,'MOVE_TABLE'));
		$('#turn').html(getTextElem(data,'TURN'));
		if(data.getElementsByTagName('MOVE_MESSAGE').length > 0)
		{
			alert(getTextElem(data,'MOVE_MESSAGE'));
		}
	}
	/*
	function sendMessage()
	{
		var msgBox = document.getElementById('messageBox');
		$.get('/Webtech/SendMessage', {msg: msgBox.value}, updateChatCallback, 'xml');
		msgBox.value = '';
	}

	function updateChat()
	{
		$.get('/Webtech/UpdateChat', updateChatCallback, 'xml');
	}

	function updateChatCallback(data)
	{
		document.getElementById('chat').innerHTML = getTextElem(data, 'CHAT');
	}
	*/
})();