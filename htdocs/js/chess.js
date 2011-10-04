var highlighted = [];
function highlightMoves(x,y)
{
	if(highlighted.length==0)
	{
		var moves = availableMoves['x'+x+'y'+y];
		if(moves != null)
		{
			for(var i=0; i < moves.length; i++)
			{
				ele = document.getElementById('x'+moves[i].x+'y'+moves[i].y);
				highlighted.push({"ele":ele,"x":moves[i].x,"y":moves[i].y});
				ele.onclick = (function(x,y,toX,toY){return function(){submitMove(x,y,toX,toY);}})(x,y, moves[i].x, moves[i].y);
				ele.innerHTML = ele.innerHTML + 'X'; 
			}
		}
	}
	else
	{
		unhighlightMoves();
	}
}
function unhighlightMoves()
{
	var h;
	while(h = highlighted.pop())
	{
		h.ele.innerHTML = h.ele.innerHTML.replace('X','');
		h.ele.onclick = (function(x,y){return function(){highlightMoves('+x+','+y+')}})(h.x,h.y);
	}
}

getTextElem = document.getElementsByTagName("body")[0].text != undefined ?
	function(ele, name)
	{
		return ele.getElementsByTagName(name)[0].text;
	}
	:
	function(ele, name)
	{
		return ele.getElementsByTagName(name)[0].textContent;
	}

function submitMove(x,y,toX,toY)
{
	var xmlHttp = getXmlHttpObject();
	xmlHttp.open('GET',submitMoveHREF+'&x='+x+'&y='+y+'&toX='+toX+'&toY='+toY,true);
	xmlHttp.onreadystatechange=function()
	{
		if(xmlHttp.readyState==4&&xmlHttp.responseXML)
		{
			updateGameCallback(xmlHttp.responseXML);
		}
	};
	xmlHttp.send(null);
	
}

function updateGameCallback(xml)
{
	unhighlightMoves();
	var tiles = xml.getElementsByTagName('TILE');
	for(var i=0;i<tiles.length;i++)
	{
		var x = getTextElem(tiles[i],'X');
		var y = getTextElem(tiles[i],'Y');
		document.getElementById('x'+x+'y'+y).innerHTML = getTextElem(tiles[i],'INNER_HTML');
		availableMoves['x'+x+'y'+y] = [];
		var moves = tiles[i].getElementsByTagName('POSSIBLE_MOVE');
		for(var j=0;j<moves.length;j++)
		{
			availableMoves['x'+x+'y'+y].push({'x':getTextElem(moves[j],'MOVE_X'),'y':getTextElem(moves[j],'MOVE_Y')});
		}
	}
	document.getElementById('moveTable').innerHTML = getTextElem(xml,'MOVE_TABLE');
	document.getElementById('turn').innerHTML = getTextElem(xml,'TURN');
	if(xml.getElementsByTagName('MOVE_MESSAGE').length > 0)
	{
		alert(getTextElem(xml,'MOVE_MESSAGE'));
	}
}

function sendMessage()
{
	var xmlHttp = getXmlHttpObject();
	xmlHttp.open('GET','/Webtech/SendMessage?msg='+encodeURIComponent(document.getElementById('messageBox').value),true);
	document.getElementById('messageBox').value = '';
	xmlHttp.onreadystatechange=function()
	{
		if(xmlHttp.readyState==4&&xmlHttp.responseXML)
		{
			updateChatCallback(xmlHttp.responseXML);
		}
	};
	xmlHttp.send();
}

function updateChat()
{
	var xmlHttp = getXmlHttpObject();
	xmlHttp.open('GET','/Webtech/UpdateChat',true);
	xmlHttp.onreadystatechange=function()
	{
		if(xmlHttp.readyState==4&&xmlHttp.responseXML)
		{
			updateChatCallback(xmlHttp.responseXML);
		}
	};
	xmlHttp.send();
}

function updateChatCallback(xml)
{
	document.getElementById('chat').innerHTML = getTextElem(xml,'CHAT');
}

