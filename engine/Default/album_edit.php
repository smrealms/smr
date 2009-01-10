<?

$smarty->assign('PageTopic','Edit Photo');

$PHP_OUTPUT.=('<p><span style="font-size:80%;">Here you have a chance to add an entry to the Space Merchant Realms - The Photo Album!<br />');
$PHP_OUTPUT.=('We only accept jpg or gif images to a maximum of 500 x 500 in size.<br />');
$PHP_OUTPUT.=('Your image will be posted under your <i>Hall Of Fame</i> nick!<br />');
$PHP_OUTPUT.=('<b>Please Note:</b> Your entry needs to be approved by an admin before going online</p>');

$PHP_OUTPUT.=('<p style="font-size:150%;">');
$PHP_OUTPUT.=('Status of your album entry: ');

$db->query('SELECT * FROM album WHERE account_id = '.SmrSession::$account_id);
if ($db->next_record()) {

	$location = stripslashes($db->f('location'));
	$email = stripslashes($db->f('email'));
	$website = stripslashes($db->f('website'));
	$day = $db->f('day');
	$month = $db->f('month');
	$year = $db->f('year');
	$other = stripslashes($db->f('other'));
	$approved = $db->f('approved');
	$disabled = $db->f('disabled');

	if ($approved == 'TBC')
		$PHP_OUTPUT.=('<span style="color:orange;">Waiting approval</span>');
	elseif ($approved == 'NO')
		$PHP_OUTPUT.=('<span style="color:red;">Approval denied</span>');
	elseif ($disabled == 'TRUE')
		$PHP_OUTPUT.=('<span style="color:red;">Disabled</span>');
	elseif ($approved == 'YES')
		$PHP_OUTPUT.=('<a href="'.$URL.'/album/?'.$account->HoF_name.'" style="color:green;">Online</a>');

} else
	$PHP_OUTPUT.=('<span style="color:orange;">No entry</span>');

$PHP_OUTPUT.=('</p>');


if (empty($location))
	$location = 'N/A';
if (empty($email))
	$email = 'N/A';
if (empty($website))
	$website = 'http://';
if (empty($day))
	$day = 'N/A';
if (empty($month))
	$month = 'N/A';
if (empty($year))
	$year = 'N/A';
if (empty($other))
	$other = 'N/A';


$PHP_OUTPUT.=create_form_parameter(create_container('album_edit_processing.php', ''), 'enctype="multipart/form-data"');
$PHP_OUTPUT.=('<table>');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td align="right" style="font-weight:bold;">Nick:</td>');
$PHP_OUTPUT.=('<td>'.$account->HoF_name.'</td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td align="right" style="font-weight:bold;">Location:</td>');
$PHP_OUTPUT.=('<td><input type="text" name="location" id="InputFields" value="'.$location.'" onFocus="javascript:if (this.value == \'N/A\') {this.value = \'\';}" onBlur="javascript:if (this.value == \'\') {this.value = \'N/A\';}"></td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td align="right" style="font-weight:bold;">Email Address:</td>');
$PHP_OUTPUT.=('<td><input type="text" name="email" id="InputFields" value="'.$email.'" style="width:303px;" onFocus="javascript:if (this.value == \'N/A\') {this.value = \'\';}" onBlur="javascript:if (this.value == \'\') {this.value = \'N/A\';}"></td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td align="right" style="font-weight:bold;">Website:</td>');
$PHP_OUTPUT.=('<td><input type="text" name="website" id="InputFields" style="width:303px;" value="'.$website.'" onBlur="javascript:if (this.value == \'\') {this.value = \'http://\';}"></td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td align="right" style="font-weight:bold;">Birthdate:</td>');
$PHP_OUTPUT.=('<td>Month:&nbsp;<input type="text" name="day" id="InputFields" value="'.$day.'" size="3" maxlength="2" style="text-align:center;" onFocus="javascript:if (this.value == \'N/A\') {this.value = \'\';}" onBlur="javascript:if (this.value == \'\') {this.value = \'N/A\';}">&nbsp;&nbsp;&nbsp;');
$PHP_OUTPUT.=('Day:&nbsp;<input type="text" name="month" id="InputFields" value="'.$month.'" size="3" maxlength="2" style="text-align:center;" onFocus="javascript:if (this.value == \'N/A\') {this.value = \'\';}" onBlur="javascript:if (this.value == \'\') {this.value = \'N/A\';}">&nbsp;&nbsp;&nbsp;');
$PHP_OUTPUT.=('Year:&nbsp;<input type="text" name="year" id="InputFields" value="'.$year.'" size="3" maxlength="4" style="text-align:center;" onFocus="javascript:if (this.value == \'N/A\') {this.value = \'\';}" onBlur="javascript:if (this.value == \'\') {this.value = \'N/A\';}"></td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td align="right" valign="top" style="font-weight:bold;">Other Info:<br /><small>(AIM/ICQ)</small></td>');
$PHP_OUTPUT.=('<td><textarea name="other" id="InputFields" style="width:303px;height:100px;" onFocus="javascript:if (this.value == \'N/A\') {this.value = \'\';}" onBlur="javascript:if (this.value == \'\') {this.value = \'N/A\';}">'.$other.'</textarea></td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td align="right" valign="top" style="font-weight:bold;">Image:</td>');
$PHP_OUTPUT.=('<td>');
if (is_readable($UPLOAD . SmrSession::$account_id))
	$PHP_OUTPUT.=('<img src="'.$URL.'/upload/'.SmrSession::$account_id.'"><br />');
$PHP_OUTPUT.=('<input type="file" name="photo" accept="image/jpeg" id="InputFields" style="width:303px;" ></td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td>&nbsp;</td>');
$PHP_OUTPUT.=('<td>');
$PHP_OUTPUT.=create_submit('Submit');
$PHP_OUTPUT.=('&nbsp;&nbsp;&nbsp;');
$PHP_OUTPUT.=create_submit('Delete Entry');
$PHP_OUTPUT.=('</td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('</table>');
$PHP_OUTPUT.=('</form>');


?>