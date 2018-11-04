// WWW: http://www.isocra.com/

var currenttable = null;

document.onmousemove = function(ev) {
	if (currenttable && currenttable.dragObject) {
		ev = ev || window.event;
		var mousePos = currenttable.mouseCoords(ev);
		var y = mousePos.y - currenttable.mouseOffset.y;

		var yOffset = window.pageYOffset;
		var currentY = mousePos.y;
		if (document.all) {
			yOffset=document.body.scrollTop;
			currentY = event.clientY;
		}
		if (currentY-yOffset < 5) {
				window.scrollBy(0, -5);
		} else {
			var windowHeight = window.innerHeight ? window.innerHeight
					: document.documentElement.clientHeight ? document.documentElement.clientHeight
							: document.body.clientHeight;
			if (windowHeight-currentY-yOffset < 5) {
				window.scrollBy(0, 5);
			}
		}

		if (y != currenttable.oldY) {
			var movingDown = y > currenttable.oldY;
			currenttable.oldY = y;
			currenttable.dragObject.style.backgroundColor.value="#aaa";
			var currentRow = currenttable.findDropTargetRow(y);
			if (currentRow) {
				if (movingDown && currenttable.dragObject != currentRow) {
					currenttable.dragObject.parentNode.insertBefore(currenttable.dragObject, currentRow.nextSibling);
				}
				else if (! movingDown && currenttable.dragObject != currentRow) {
					currenttable.dragObject.parentNode.insertBefore(currenttable.dragObject, currentRow);
				}
			}
		}

		return false;
	}
};

document.onmouseup = function(ev){
	if (currenttable && currenttable.dragObject) {
		var droppedRow = currenttable.dragObject;
		droppedRow.style.backgroundColor = 'transparent';
		currenttable.dragObject = null;
		currenttable.onDrop(currenttable.table, droppedRow);
		currenttable = null;
	}
};

function getEventSource(evt) {
	if (window.event) {
		evt = window.event;
		return evt.srcElement;
	} else {
		return evt.target;
	}
}

function TableDnD() {
	this.dragObject = null;
	this.mouseOffset = null;
	this.table = null;
	this.oldY = 0;

	this.init = function(table) {
		this.table = table;
		var rows = table.tBodies[0].rows;
		for (var i=0; i<rows.length; i++) {
			var nodrag = rows[i].getAttribute("NoDrag");
			if (nodrag == null || nodrag == "undefined") {
				this.makeDraggable(rows[i]);
			}
		}
	};

	this.onDrop = function(table, droppedRow) {};

	this.getPosition = function(e) {
		var left = 0;
		var top = 0;
		if (e.offsetHeight == 0) {
			e = e.firstChild;
		}
		while (e.offsetParent) {
			left += e.offsetLeft;
			top += e.offsetTop;
			e = e.offsetParent;
		}
		left += e.offsetLeft;
		top += e.offsetTop;
		return {x:left, y:top};
	};
	this.mouseCoords = function(ev) {
		if(ev.pageX || ev.pageY) {
			return {x:ev.pageX, y:ev.pageY};
		}
		return {x:ev.clientX + document.body.scrollLeft - document.body.clientLeft,y:ev.clientY + document.body.scrollTop - document.body.clientTop};
	};
	this.getMouseOffset = function(target, ev) {
		ev = ev || window.event;
		var docPos = this.getPosition(target);
		var mousePos = this.mouseCoords(ev);
		return {x:mousePos.x - docPos.x, y:mousePos.y - docPos.y};
	};
	this.makeDraggable = function(item) {
		if(!item) return;
		var self = this;
		item.onmousedown = function(ev) {
			var target = getEventSource(ev);
			if (target.tagName == 'INPUT' || target.tagName == 'SELECT') return true;
			currenttable = self;
			self.dragObject = this;
			self.mouseOffset = self.getMouseOffset(this, ev);
			return false;
		};
		item.style.cursor="move";
	};
	this.findDropTargetRow = function(y) {
		var rows = this.table.tBodies[0].rows;
		for (var i=0; i<rows.length; i++) {
			var row = rows[i];
			var nodrop = row.getAttribute("NoDrop");
			if (nodrop == null || nodrop == "undefined") {
				var rowY	= this.getPosition(row).y;
				var rowHeight = parseInt(row.offsetHeight)/2;
				if (row.offsetHeight == 0) {
					rowY = this.getPosition(row.firstChild).y;
					rowHeight = parseInt(row.firstChild.offsetHeight)/2;
				}
				if ((y > rowY - rowHeight) && (y < (rowY + rowHeight))) {
					return row;
				}
			}
		}
		return null;
	};
}

var table = document.getElementById('weapon_reorder');
var tableDnD = new TableDnD();
tableDnD.init(table);

moveRow=function(cell, move) {
	var currentRow = cell.parentNode;
	var currentRowID = false;
	var rows = currentRow.parentNode.rows;
	for(var i = 1; i < rows.length; i++) {
		if(rows[i] == currentRow) currentRowID = i;
	}
	if(currentRowID==false) return;
	if(move>0)
		move++;
	var newRowID = currentRowID+move;
	if(newRowID>rows.length)
		newRowID = 1;
	else if(newRowID<1)
		newRowID = rows.length;

	currentRow.parentNode.insertBefore(currentRow, rows[newRowID]);
};

function doSubmit() {
	var rows = document.getElementById('weapon_reorder').rows;
	var ret = new Array();
	for(var i = 0; i < rows.length;i++) {
		ret[ret.length] = rows[i].getElementsByTagName('td')[0].innerHTML;
	}
	return ret.join('|');
}
