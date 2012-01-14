//smr.js
function voteSite(url,snUrl) {
	window.open(url);
	window.location=snUrl;
}

var intervalCalc,intervalM,intervalRace;
function calc() {
	var i = 1, total = 0, df=document.FORM;
	for(; i<=9; i++) {
		total += df['port'+i].value * 1;
	}
	df.total.value = total;
}
function startCalc() {
	intervalCalc = setInterval(calc,1);
}
function stopCalc() {
	clearInterval(intervalCalc);
}
function calcM() {
	var i = 1, total = 0, df=document.FORM;
	for(; i<=20; i++) {
		total += df['mine'+i].value * 1;
	}
	df.totalM.value = total;
}
function startCalcM() {
	intervalM = setInterval(calcM,1);
}
function stopCalcM() {
	clearInterval(intervalM);
}
function set_even()
{
	var i = 2, df=document.FORM;
	df['race1'].value = 12;
	for(; i<=9; i++) {
		df['race'+i].value = 11;
	}
	df.racedist.value = 100;
}
function Racecalc() {
	var i = 1, total = 0, df=document.FORM;
	for(; i<=9; i++) {
		total += df['race'+i].value * 1;
	}
	df.racedist.value = total;
}
function startRaceCalc() {
	intervalRace = setInterval(Racecalc,1);
}
function stopRaceCalc() {
	clearInterval(intervalRace);
}

var body, currentlyFlashing=false, flashColour, origColour, intervalFlash, timeoutStopFlash;

function stopFlash() {
	clearInterval(intervalFlash);
	body.style.backgroundColor = origColour;
	currentlyFlashing = false;
}

function bgFlash() {
	if(body.style.backgroundColor === origColour) {
		body.style.backgroundColor = flashColour;
	}
	else {
		body.style.backgroundColor = origColour;
	}
}

function TriggerAttackBlink(colour) {
	if(body == null) {
		body = document.getElementsByTagName('body')[0];
		origColour = body.style.backgroundColor;
	}
	flashColour = '#'+colour;
	clearTimeout(timeoutStopFlash);
	if (currentlyFlashing === false) {
		currentlyFlashing = true;
		//flash 3 times
		bgFlash();
		intervalFlash = setInterval(bgFlash,750);
	}
	timeoutStopFlash = setTimeout(stopFlash,5250);
}