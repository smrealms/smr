(function() {
"use strict";

	window.voteSite = function(snUrl) {
		// Must not redirect the current page until after the external vote
		// site URL has been opened in a new tab. Use setTimeout to do this.
		setTimeout(function() {
			window.location = snUrl;
		}, 0);
	};

	function doCalc(type, number, totalDest) {
		var i = 1, total = 0, df=document.FORM;
		for(; i<=number; i++) {
			total += df[type+i].value * 1;
		}
		df[totalDest].value = total;
	};

	// Recalculate total number of ports, summing over level
	window.levelCalc = function(maxPortLevel) {
		doCalc('port', maxPortLevel, 'total');
	};

	// Recalculate sum of port race percentages
	window.raceCalc = function() {
		doCalc('race', 9, 'racedist');
	};

	// Set the total number of ports to zero
	window.setZero = function(maxPortLevel) {
		var df = document.FORM;
		for (var i=1; i<=maxPortLevel; i++) {
			df['port'+i].value = 0;
		}
		df.total.value = 0;
	};

	// Set the port race distribution to be equal
	window.setEven = function() {
		var i = 2, df=document.FORM;
		df.race1.value = 12;
		for(; i<=9; i++) {
			df['race'+i].value = 11;
		}
		df.racedist.value = 100;
	};

	var body, currentlyFlashing=false, flashColour, origColour, intervalFlash, timeoutStopFlash;

	function stopFlash() {
		clearInterval(intervalFlash);
		body.style.backgroundColor = origColour;
		currentlyFlashing = false;
	};

	function bgFlash() {
		var body = document.getElementsByTagName('body')[0];
		if(body.style.backgroundColor === origColour) {
			body.style.backgroundColor = flashColour;
		}
		else {
			body.style.backgroundColor = origColour;
		}
	};

	window.triggerAttackBlink = function(colour) {
		if(origColour == null) {
			origColour = document.getElementsByTagName('body')[0].style.backgroundColor;
		}
		flashColour = '#'+colour;
		clearTimeout(timeoutStopFlash);
		if (currentlyFlashing === false) {
			currentlyFlashing = true;
			//flash 3 times
			bgFlash();
			intervalFlash = setInterval(bgFlash,500);
		}
		timeoutStopFlash = setTimeout(stopFlash,3500);
	};
})();

// Used by shop_hardware.php
function recalcOnKeyUp(transaction, hardwareTypeID, cost) {
	var form = document.getElementById(transaction + hardwareTypeID);
	form.total.value = form.amount.value * cost;
}

// Used by planet_defense.php
function showWeaponInfo(select) {
	var target = $(select).data('target');
	var show = $("option:selected", select).data('show');
	$(target).children().addClass('hide');
	$(show).removeClass('hide');
}

// Used by game_join.php
function showRaceInfo(select) {
	var race_id = $("option:selected", select).val();
	document.getElementById('race_image').src = "images/race/race" + race_id + ".jpg";
	document.getElementById('graphframe').src = "images/race/graph/graph" + race_id + ".gif";
	var desc = document.getElementById('race_descr');
	$(desc).children().addClass('hide');
	$(".race_descr" + race_id, desc).removeClass('hide');
	graph_by_plotly(race_id);
}

// Used by game_join.php 
function graph_by_plotly(race_id) {

	var races = [["[1.0,1.0,1.0,1.0,1.0]","'Neutral'","'#FFD800'"],
	[[1.0,1.0,1.0,1.0,1.0],'Neutral','#FFD800'],
	[[5.2, 10.0, 5.8, 8.1, 5.2],'Alaskant','#FF00FF'],
	[[8.3, 5.0, 10.0, 3.3, 8.3],'Creonti','#FF8000'],
	[[9.5, 4.9, 9.5, 7.7, 9.5],'Human','#0000FF'],
	[[8.0, 6.1, 9.7, 10.0, 8.0],'Ik\'Thorne','#BFBFFF'],
	[[8.1, 4.7, 8.8, 4.3, 8.1],'Salvene','#00AA00'],
	[[10.0, 5.7, 8.7, 9.1, 10.0],'Thevian','#800000'],
	[[7.2, 6.3, 7.9, 6.2, 7.2],'WQ Human','#804040'],
	[[9.5, 4.9, 8.0, 5.1, 9.5],'Nijarin','#FF8080']];
	data = [
		{
			type: 'scatterpolar',
			r: races[race_id][0],
			theta: ['Hunting','Trading','Combat', 'Utility', 'Hunting'],
			fill: 'toself',
			name: races[race_id][1],
			line: {color: races[race_id][2], dash: 'longdash'},
			title: {
				text: races[race_id][1]
			}
		}
	];

	layout = {
		showlegend: true,
		polar: {
			radialaxis: {
				visible: true,
				showgrid: true,
				range: [0, 11],
				color: '#000',
				plot_bgcolor: '#000',
				bgcolor: '#000',
			},
		},
		paper_bgcolor: '#06240E',
		plot_bgcolor: '#000',
		font: {
			color: '#fff'
		},
		coloraxis : '#000'
	};
	Plotly.newPlot("graphframe", data, layout, {staticPlot: true});
}
// Used by alliance_create.php and alliance_stat.php
function togglePassword(select) {
	var showPassword = $(select).val() === "password";
	// We need to both toggle the element display (for the user) and toggle the
	// disabled property (for the form submission).
	if (showPassword) {
		$("#password-display").show();
	} else {
		$("#password-display").hide();
	}
	$("#password-input").prop("disabled", !showPassword);
}
