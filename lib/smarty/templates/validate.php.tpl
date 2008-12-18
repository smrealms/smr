<h1>VALIDATION REMINDER</h1><br />

<form name="FORM" method="POST" action="{$ValidationFormAction}">
<input type="hidden" name="sn" value="{$ValidateFormSN}">

<p>Welcome {$FirstName}</p>
<p>
Thank you for trying out Space Merchant Realms! We hope that you are enjoying the game. However,
in order for you to experience the full features of the game, you need to validate your login.
When you first created your login, you should have received an email confirmation which includes
your validation code. If you have not received this, please verify that you gave us the correct
email address by going to the user preferences page. If it
is incorrect, please edit the email address and it will generate a new code and have it sent to
you.
</p>
<p>
The following restrictions are placed on users who have not validated their account:
<ul>
<li>No additional turns are granted to your traders while you are not validated.
<li>Bank access is denied.
<li>You will be unable to land on a planet.
<li>You will be unable to access alliances.
<li>You will be unable to vote in the daily politics of the universe.
</ul>
</p>
<p>
Enter validation code:&nbsp;&nbsp;
<input type="text" name="validation_code" maxlength="10" size="10" id="InputFields" style="text-align:center;">
</p>
<p align="center">
<input type="submit" name="action" value="Validate me now!" id="InputFields">
&nbsp;&nbsp;
<input type="submit" name="action" value="I'll validate later." id="InputFields">
</p>
</form>