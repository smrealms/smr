// For use with {ship,weapon}_list.php

// Use window variable to store filter values. Since this is used for both
// the ship and weapon tables, it needs to have enough elements for either.
window.filter = ["All", "All", "All", "All", "All", "All", "All", "All", "All", "All",
                 "All", "All", "All", "All", "All", "All", "All", "All", "All"];

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

	window.filter[columnId] = selected;
	applyFilter('data-list');
}

function raceToggle() {
	var toggle = document.getElementById("raceform");
	window.filter[1] = [];
	for (var i = 0; i < toggle.races.length; i++) {
		if (toggle.races[i].checked) {
			window.filter[1].push(toggle.races[i].value);
		}
	}
	applyFilter('data-list');
}

function applyFilter(tableId) {
	var table = document.getElementById(tableId);
	for (var i=1; i < table.rows.length; i++) {
		var show = true;
		for (var j=0; j < table.rows[i].cells.length; j++) {
			if (window.filter[j] == "All") {
				continue;
			}
			if (Array.isArray(window.filter[j])) {
				if (window.filter[j].indexOf(table.rows[i].cells[j].textContent) === -1) {
					show = false;
					break;
				}
			} else {
				if (table.rows[i].cells[j].textContent != window.filter[j]) {
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
