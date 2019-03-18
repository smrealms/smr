// Benoit Asselin - http://www.ab-d.fr
Array.prototype.in_array = function(p_val) {
	for(var i = 0, l = this.length; i < l; i++) {
		if(this[i] == p_val) {
			return true;
		}
	}
	return false;
}
//JS code by Astax to foster filtering the results

//Use window variable to store filter values, this is kinda like a JS equivellent of global
window.filter = new Array("All", "All", "All", "All", "All", "All", "All", "All", "All", "All");

//reset all check boxes
function resetBoxes() {
	var toggle = document.getElementById("raceform");
	for (i = 0; i < toggle.races.length; i++) {
		toggle.races[i].checked = true;
	}
}

function racePickf() {
	filterSelect("racePick", 1);
}

function powerPickf() {
	filterSelect("powerPick", 6);
}

function restrictPickf() {
	filterSelect("restrictPick", 7);
}

function filterSelect(selectId, filterId) {
	var option = document.getElementById(selectId);
	var selected = option.options[option.selectedIndex].value;

	window.filter[filterId] = selected;
	applyFilter();
}

function raceToggle() {
	var toggle = document.getElementById("raceform");
	window.filter[1] = new Array();
	for (i = 0; i < toggle.races.length; i++) {
		if (toggle.races[i].checked) {
			window.filter[1].push(toggle.races[i].value);
		}
	}
	applyFilter();
}

function applyFilter() {
	var table = document.getElementById("table");
	for (var i=1; i < table.rows.length; i++) {
		var show = true;
		for (var j=0; j < table.rows[i].cells.length; j++) {
			if (window.filter[j] == "All") {
				continue;
			}
			if( Object.prototype.toString.call( window.filter[j] ) === '[object Array]' ) {
				if (!window.filter[j].in_array(table.rows[i].cells[j].innerHTML)) {
					show = false;
					break;
				}
			} else {
				if (table.rows[i].cells[j].innerHTML != window.filter[j]) {
					show = false;
					break;
				}
			}
		}
		if (show) {
			table.rows[i].style.display="";
		} else {
			table.rows[i].style.display="none";
		}
	}
}
