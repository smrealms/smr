(function() {
"use strict";

	window.voteSite = function(url,snUrl) {
		window.open(url);
		window.location=snUrl;
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
