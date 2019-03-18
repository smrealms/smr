//JS code by Astax to foster filtering the results

//Use window variable to store filter values, this is kinda like a JS equivellent of global
window.filter = ["All", "All", "All", "All", "All", "All", "All", "All", "All", "All",
                 "All", "All", "All", "All", "All", "All", "All", "All", "All"];

function classPickf() {
	filterSelect("classPick", 2);
}

function racePickf() {
	filterSelect("racePick", 1);
}

function speedPickf() {
	filterSelect("speedPick", 4);
}

function hpPickf() {
	filterSelect("hpPick", 5);
}

function restrictPickf() {
	filterSelect("restrictPick", 6);
}

function scannerPickf() {
	filterSelect("scannerPick", 13);
}

function cloakPickf() {
	filterSelect("cloakPick", 14);
}

function illusionPickf() {
	filterSelect("illusionPick", 15);
}

function jumpPickf() {
	filterSelect("jumpPick", 16);
}

function scramblePickf() {
	filterSelect("scramblePick", 17);
}

function filterSelect(selectId, filterId) {
	var option = document.getElementById(selectId);
	var selected = option.options[option.selectedIndex].value;

	window.filter[filterId] = selected;
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
			if (table.rows[i].cells[j].innerHTML != window.filter[j]) {
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
