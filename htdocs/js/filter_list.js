// For use with {ship,weapon}_list.php

var filter = [];

//reset all check boxes
function resetBoxes() {
	var toggle = document.getElementById("raceform");
	for (var i = 0; i < toggle.races.length; i++) {
		toggle.races[i].checked = true;
	}
}

function filterSelect(element) {
	var selected = element.options[element.selectedIndex].value;
	var columnId = element.parentElement.cellIndex;

	filter[columnId] = selected;
	applyFilter('data-list');
}

function raceToggle() {
	var toggle = document.getElementById("raceform");
	// 1 is the index of the "Race" column
	filter[1] = [];
	for (var i = 0; i < toggle.races.length; i++) {
		if (toggle.races[i].checked) {
			filter[1].push(toggle.races[i].value);
		}
	}
	applyFilter('data-list');
}

function applyFilter(tableId) {
	var table = document.getElementById(tableId);
	for (var i=1; i < table.rows.length; i++) {
		var show = true;
		for (var j=0; j < table.rows[i].cells.length; j++) {
			// No filtering for null, undefined, and "All".
			// But we do filter on the empty string (for the "None" option).
			if (filter[j] == null || filter[j] === "All") {
				continue;
			}
			if (Array.isArray(filter[j])) {
				if (filter[j].indexOf(table.rows[i].cells[j].textContent) === -1) {
					show = false;
					break;
				}
			} else {
				if (table.rows[i].cells[j].textContent != filter[j]) {
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
