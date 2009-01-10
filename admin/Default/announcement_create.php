<?

$smarty->assign('PageTopic','Create Announcement');

$PHP_OUTPUT.=create_echo_form(create_container('announcement_create_processing.php', ''));
$PHP_OUTPUT.=('<textarea name="message" id="InputFields" style="width:350px;height:100px;"></textarea><br />');
$PHP_OUTPUT.=create_submit('Create');
$PHP_OUTPUT.=('</form>');

?>
