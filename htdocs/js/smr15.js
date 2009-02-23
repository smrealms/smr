//smr.js
function VoteSite(url,sn) {
	window.open(url);
	str = 'loader.php?sn=' + sn; 
	window.location=str;
}


//smr15.js
/*
Wep Drag Adapted for SMR use by Azool.
Original Source from http://www.cyberdummy.co.uk/test/dd.php
*/

var ajaxRunning = true;

window.onblur = function()
{
    // Pause the Ajax updates
    ajaxRunning = false;
};
window.onfocus = function()
{
    // Start the Ajax updates
    ajaxRunning = true;
};

function stopAJAX()
{
	ajaxRunning = false;
}

//var onClickAdded = false;
//function addOnClickToLinks()
//{
//	if(!onClickAdded)
//	{
//		onClickAdded = true;
//		var aLinks = document.getElementsByTagName( 'a' );
//		for( var i = 0; i < aLinks.length; i++ )
//		{
//			aLinks[i].onclick = stopAJAX;
//		}
//	}
//}

var currentlyFlashing=false;
function TriggerAttackBlink(colour)
{
	if (old_shields != shields) shi_hit = 1;
	if (old_cd != cd) cd_hit = 1;
	if (old_armour != armour) arm_hit = 1;
	
	if (shi_hit == 1)
		document.getElementById("shields").innerHTML='<span class="red">' + shields + '/' + max_shields + '</span>';
	if (cd_hit == 1)
		document.getElementById("cds").innerHTML='<span class="red">' + cd + '/' + max_cd + '</span>';
	if (arm_hit == 1)
		document.getElementById("armour").innerHTML='<span class="red">' + armour + '/' + max_armour + '</span>';
		
	document.getElementById("attack_area").innerHTML= '<div class="attack_warning">You Are Under Attack!</div>';
	color = xmlDoc.getElementsByTagName("attack")[0].childNodes[0].nodeValue;
	if (currentlyFlashing == false)
	{
		currentlyFlashing = true;
		//flash 3 times
		setTimeout('ajax_flash1(\'' + colour + '\')',0);
		setTimeout('ajax_flash1(\'' + colour + '\')',2000);
		setTimeout('ajax_flash1(\'' + colour + '\')',4000);

		setTimeout('ajax_flash2()',1000);
		setTimeout('ajax_flash2()',3000);
		setTimeout('ajax_flash2()',5000);
		setTimeout('stopFlash()',5000);
	}
}

function stopFlash()
{
	currentlyFlashing = false;
}

function ajax_flash1(color)
{
	document.bgColor="#" + color;
}

function ajax_flash2()
{
	document.bgColor="#0B2121";
}

function nothing() {}

function startCalc(){
	interval = setInterval("calc()",1);
}
function calc(){
	one = document.form.port1.value;
	two = document.form.port2.value;
	three = document.form.port3.value;
	four = document.form.port4.value;
	five = document.form.port5.value;
	six = document.form.port6.value;
	seven = document.form.port7.value;
	eight = document.form.port8.value;
	nine = document.form.port9.value;
	document.form.total.value = (one * 1) + (two * 1) + (three * 1) + (four * 1) + (five * 1) + (six * 1) + (seven * 1) + (eight * 1) + (nine * 1);
}
function stopCalc(){
	clearInterval(interval);
}
function startCalcM(){
	intervalM = setInterval("calcM()",1);
}
function calcM(){
	one = document.form.mine1.value;
	two = document.form.mine2.value;
	three = document.form.mine3.value;
	four = document.form.mine4.value;
	five = document.form.mine5.value;
	six = document.form.mine6.value;
	seven = document.form.mine7.value;
	eight = document.form.mine8.value;
	nine = document.form.mine9.value;
	ten = document.form.mine10.value;
	ele = document.form.mine11.value;
	twe = document.form.mine12.value;
	thir = document.form.mine13.value;
	fourt = document.form.mine14.value;
	fift = document.form.mine15.value;
	sixt = document.form.mine16.value;
	sevent = document.form.mine17.value;
	eighte = document.form.mine18.value;
	ninete = document.form.mine19.value;
	twent = document.form.mine20.value;
	document.form.totalM.value = (one * 1) + (two * 1) + (three * 1) + (four * 1) + (five * 1) + (six * 1) + (seven * 1) + (eight * 1) + (nine * 1) + (ten * 1) + (ele * 1) + (twe * 1) + (thir * 1) + (fourt * 1) + (fift * 1) + (sixt * 1) + (sevent * 1) + (eighte * 1) + (ninete * 1) + (twent * 1);
}
function stopCalcM(){
	clearInterval(intervalM);
}
function set_even(){
	document.form.race0.value = 12;
	document.form.race10.value = 11;
	document.form.race20.value = 11;
	document.form.race30.value = 11;
	document.form.race40.value = 11;
	document.form.race50.value = 11;
	document.form.race60.value = 11;
	document.form.race70.value = 11;
	document.form.race80.value = 11;
	document.form.racedist.value = 100;
}
function startRaceCalc(){
	intervalRace = setInterval("Racecalc()",1);
}
function Racecalc(){
	one = document.form.race0.value;
	two = document.form.race10.value;
	three = document.form.race20.value;
	four = document.form.race30.value;
	five = document.form.race40.value;
	six = document.form.race50.value;
	seven = document.form.race60.value;
	eight = document.form.race70.value;
	nine = document.form.race80.value;
	document.form.racedist.value = (one * 1) + (two * 1) + (three * 1) + (four * 1) + (five * 1) + (six * 1) + (seven * 1) + (eight * 1) + (nine * 1);
}
function stopRaceCalc(){
	clearInterval(intervalRace);
}

/* weapon toggle */
function toggleWepD(amount)
{
	for(var i = 1; i <= amount; i++)
	{
		if (document.getElementById('wep_item' + i).style.display == 'none')
			document.getElementById('wep_item' + i).style.display = 'block';
		else
			document.getElementById('wep_item' + i).style.display = 'none';
	}
}

//startRP();
//startCS();