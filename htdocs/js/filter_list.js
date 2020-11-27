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

	filter[columnId] = [selected];
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
		// Loop over columns with active filters, and hide row if any column
		// fails its respective filter.
		var show = true;
		for (var j=0; j < table.rows[i].cells.length; j++) {
			// No filtering for null, undefined, and "All".
			// But we do filter on the empty string (for the "None" option).
			if (filter[j] == null || filter[j][0] === "All") {
				continue;
			}

			// Prepare the list of values in this cell for filtering.
			// If there are no divs, then entire cell is the only value.
			var cell = table.rows[i].cells[j];
			var values = cell.getElementsByTagName('div');
			if (values.length === 0) {
				values = [cell];
			}

			// The filters match against raw values, so extract them here.
			var rawValues = [];
			for (var k=0; k < values.length; k++) {
				rawValues.push(values[k].textContent);
			}

			// One of the values must match at least one of the filters
			if (rawValues.filter(function(value) { return filter[j].indexOf(value) !== -1; }).length === 0) {
				show = false;
				break;
			}
		}
		if (show) {
			table.rows[i].style.display="";
		} else {
			table.rows[i].style.display="none";
		}
	}
}
