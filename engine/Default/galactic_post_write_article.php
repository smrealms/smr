<?

$template->assign('PageTopic','WRITTING AN ARTICLE');
include(get_file_loc('menue.inc'));
$PHP_OUTPUT.=create_galactic_post_menue();
$PHP_OUTPUT.=('What is the title?<br />');
$container = array();
$container['url'] = 'galactic_post_write_article_processing.php';
$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=('<input type="text" name="title" id="InputFields" style="text-align:center;width:525;"><br /><br />');
$PHP_OUTPUT.=('<br />Write what you want to write here!<br />');
$PHP_OUTPUT.=('<textarea name="message" id="InputFields" cols="20" rows="30"></textarea><br /><br />');
$PHP_OUTPUT.=create_submit('Enter the article');
$PHP_OUTPUT.=('</form>');

?>