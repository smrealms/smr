function OpenWindow(linkName)
{
	var popUpWindow = window.open(linkName, 'chat', 'resizeable, toolbar=no, location=no, directories=no, status=no, menubar=no, copyhistory=no, width=575, height=425');
	popUpWindow.focus();
} 

var AttackToggle = false;

function AttackBlink()
{

	var ele = document.getElementById('attack_warning');

	AttackToggle = !AttackToggle;
	if (AttackToggle === false)
	{
		ele.style.color = '#ff0000';
	}
	else if (AttackToggle === true)
	{
		ele.style.color = '#aa0000';
	}
}

function SetBlink()
{
	setInterval(AttackBlink,250);
}

function DoSubmit(action,form_id)
{
	var form = document.forms[form_id];
	var input = document.createElement('input');
	input.setAttribute('type', 'hidden');
	input.setAttribute('name', 'action');
	input.setAttribute('value', action);
	form.appendChild(input);
	form.submit();
}