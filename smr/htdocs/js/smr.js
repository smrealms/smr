function OpenWindow(linkName) {
popUpWindow = window.open(linkName, 'chat', 'resizeable, toolbar=no, location=no, directories=no, status=no, menubar=no, copyhistory=no, width=575, height=425')
popUpWindow.focus(); } 

var AttackToggle = 0;

function AttackBlink() {

	ele = document.getElementById('attack_warning');

	AttackToggle = 1 - AttackToggle;
	if (AttackToggle == 0) {
		ele.style.color = '#ff0000';
	}
	else if (AttackToggle == 1) {
		ele.style.color = '#aa0000';
	}
}

function SetBlink() {
	setInterval('AttackBlink()',250);
}

function DoSubmit(action,form_id) {
	var form = document.forms[form_id];
	var input = document.createElement('input');
	input.setAttribute('type', 'hidden');
	input.setAttribute('name', 'action');
	input.setAttribute('value', action);
	form.appendChild(input);
	form.submit();
}

function DoOnload() {
return;
	var buttons = document.getElementsByName('action');
	//var num_buttons = buttons.length;
	while(buttons.length) {
		var text = document.createTextNode(buttons[0].value)
		var button = document.createElement('div');
		button.id = 'buttonA';
		button.innerHTML = '<a href="javascript:DoSubmit('' + buttons[0].value + '','' + buttons[0].parentNode.id + '')">' + buttons[0].value + '</a>';
		buttons[0].parentNode.insertBefore(button,buttons[0]);
		buttons[0].parentNode.removeChild(buttons[0]);

	}
	document.cookie='Legit=0';
}

window.onload = DoOnload;