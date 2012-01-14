(function() {
"use strict";

	var intervalCalc, intervalM, intervalRace, calc, calcM, raceCalc;
	
	window.voteSite = function(url,snUrl) {
		window.open(url);
		window.location=snUrl;
	};

	calc = function() {
		var i = 1, total = 0, df=document.FORM;
		for(; i<=9; i++) {
			total += df['port'+i].value * 1;
		}
		df.total.value = total;
	};
	window.startCalc = function() {
		intervalCalc = setInterval(calc,10);
	};
	window.stopCalc = function() {
		clearInterval(intervalCalc);
	};
	
	calcM = function() {
		var i = 1, total = 0, df=document.FORM;
		for(; i<=20; i++) {
			total += df['mine'+i].value * 1;
		}
		df.totalM.value = total;
	};
	window.startCalcM = function() {
		intervalM = setInterval(calcM,10);
	};
	window.stopCalcM = function() {
		clearInterval(intervalM);
	};
	window.setEven = function() {
		var i = 2, df=document.FORM;
		df.race1.value = 12;
		for(; i<=9; i++) {
			df['race'+i].value = 11;
		}
		df.racedist.value = 100;
	};
	
	raceCalc = function() {
		var i = 1, total = 0, df=document.FORM;
		for(; i<=9; i++) {
			total += df['race'+i].value * 1;
		}
		df.racedist.value = total;
	};
	window.startRaceCalc = function() {
		intervalRace = setInterval(raceCalc,10);
	};
	window.stopRaceCalc = function() {
		clearInterval(intervalRace);
	};

	var body, currentlyFlashing=false, flashColour, origColour, intervalFlash, timeoutStopFlash, stopFlash, bgFlash;

	stopFlash = function() {
		clearInterval(intervalFlash);
		body.style.backgroundColor = origColour;
		currentlyFlashing = false;
	};

	bgFlash = function() {
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