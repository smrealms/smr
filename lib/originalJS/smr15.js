//smr.js
function voteSite(url,snUrl)
{
	window.open(url);
	window.location=snUrl;
}

var intervalCalc,intervalM,intervalRace;
function calc()
{
	var one,two,three,four,five,six,seven,eight,nine,df=document.FORM;
	one = df.port1.value;
	two = df.port2.value;
	three = df.port3.value;
	four = df.port4.value;
	five = df.port5.value;
	six = df.port6.value;
	seven = df.port7.value;
	eight = df.port8.value;
	nine = df.port9.value;
	df.total.value = (one * 1) + (two * 1) + (three * 1) + (four * 1) + (five * 1) + (six * 1) + (seven * 1) + (eight * 1) + (nine * 1);
}
function startCalc()
{
	intervalCalc = setInterval(calc,1);
}
function stopCalc()
{
	clearInterval(intervalCalc);
}
function calcM()
{
	var one,two,three,four,five,six,seven,eight,nine,ten,ele,twe,thir,fourt,fift,sixt,sevent,eighte,ninete,twent,df=document.FORM;
	one = df.mine1.value;
	two = df.mine2.value;
	three = df.mine3.value;
	four = df.mine4.value;
	five = df.mine5.value;
	six = df.mine6.value;
	seven = df.mine7.value;
	eight = df.mine8.value;
	nine = df.mine9.value;
	ten = df.mine10.value;
	ele = df.mine11.value;
	twe = df.mine12.value;
	thir = df.mine13.value;
	fourt = df.mine14.value;
	fift = df.mine15.value;
	sixt = df.mine16.value;
	sevent = df.mine17.value;
	eighte = df.mine18.value;
	ninete = df.mine19.value;
	twent = df.mine20.value;
	df.totalM.value = (one * 1) + (two * 1) + (three * 1) + (four * 1) + (five * 1) + (six * 1) + (seven * 1) + (eight * 1) + (nine * 1) + (ten * 1) + (ele * 1) + (twe * 1) + (thir * 1) + (fourt * 1) + (fift * 1) + (sixt * 1) + (sevent * 1) + (eighte * 1) + (ninete * 1) + (twent * 1);
}
function startCalcM()
{
	intervalM = setInterval(calcM,1);
}
function stopCalcM()
{
	clearInterval(intervalM);
}
function set_even()
{
	var df=document.FORM;
	df.race1.value = 12;
	df.race2.value = 11;
	df.race3.value = 11;
	df.race4.value = 11;
	df.race5.value = 11;
	df.race6.value = 11;
	df.race7.value = 11;
	df.race8.value = 11;
	df.race9.value = 11;
	df.racedist.value = 100;
}
function Racecalc()
{
	var one,two,three,four,five,six,seven,eight,nine,df=document.FORM;
	one = df.race1.value;
	two = df.race2.value;
	three = df.race3.value;
	four = df.race4.value;
	five = df.race5.value;
	six = df.race6.value;
	seven = df.race7.value;
	eight = df.race8.value;
	nine = df.race9.value;
	df.racedist.value = (one * 1) + (two * 1) + (three * 1) + (four * 1) + (five * 1) + (six * 1) + (seven * 1) + (eight * 1) + (nine * 1);
}
function startRaceCalc()
{
	intervalRace = setInterval(Racecalc,1);
}
function stopRaceCalc()
{
	clearInterval(intervalRace);
}

var body, currentlyFlashing=false, flashColour, origColour, intervalFlash, timeoutStopFlash;

function stopFlash()
{
	clearInterval(intervalFlash);
	body.style.backgroundColor = origColour;
	currentlyFlashing = false;
}

function bgFlash()
{
	if(body.style.backgroundColor === origColour) {
		body.style.backgroundColor = flashColour;
	}
	else {
		body.style.backgroundColor = origColour;
	}
}

function TriggerAttackBlink(colour)
{
	if(body == null)
	{
		body = document.getElementsByTagName('body')[0];
		origColour = body.style.backgroundColor;
	}
	flashColour = '#'+colour;
	clearTimeout(timeoutStopFlash);
	if (currentlyFlashing === false)
	{
		currentlyFlashing = true;
		//flash 3 times
		bgFlash();
		intervalFlash = setInterval(bgFlash,750);
	}
	timeoutStopFlash = setTimeout(stopFlash,5250);
}