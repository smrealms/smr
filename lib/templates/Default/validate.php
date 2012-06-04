<h1>VALIDATION REMINDER</h1><br />

<form name="FORM" method="POST" action="<?php echo $ValidateFormHref ?>">

<?php
if(isset($Message))
{
	echo $Message; ?><br /><?php
} ?>

<p>Welcome <?php echo $FirstName ?></p>
<p>
	Thank you for trying out Space Merchant Realms! We hope that you are enjoying the game. However,
	in order for you to experience the full features of the game, you need to validate your login.
	When you first created your login, you should have received an email confirmation which includes
	your validation code. if(you have not received this, please verify that you gave us the correct
	email address by going to the user preferences page. if(it
	is incorrect, please edit the email address and it will generate a new code and have it sent to
	you.
</p>
<p>
	The following restrictions are placed on users who have not validated their account:
</p>
<ul>
	<li>No additional turns are granted to your traders while you are not validated.</li>
	<li>Bank access is denied.</li>
	<li>You will be unable to land on a planet.</li>
	<li>You will be unable to access alliances.</li>
	<li>You will be unable to vote in the daily politics of the universe.</li>
</ul>
<p>
	Enter validation code:&nbsp;&nbsp;
	<input type="text" name="validation_code" maxlength="10" size="10" class="InputFields" style="text-align:center;">
</p>
<p align="center">
	<input type="submit" name="action" value="Validate me now!" class="InputFields">
	&nbsp;&nbsp;
	<input type="submit" name="action" value="I'll validate later." class="InputFields">
</p>
</form>