<?

$template->assign('PageTopic','EDITING AN ARTICLE');
include(ENGINE . 'global/menue.inc');
$PHP_OUTPUT.=create_galactic_post_menue();
$db->query('SELECT * FROM galactic_post_article WHERE game_id = '.$player->getGameID().' AND article_id = '.$var['id']);
$db->nextRecord();
$title = $db->getField('title');
$text = $db->getField('text');
$container = array();
$container['url'] = 'galactic_post_edit_article_processing.php';
transfer('id');
$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=('What is the title?<br />');
$PHP_OUTPUT.=('<input type="text" name="title" align="center" value="'.$title.'" id="InputFields" style="text-align:center;width:525;"><br /><br />');
$PHP_OUTPUT.=('<br />Write what you want to write here!<br />');
$PHP_OUTPUT.=('<textarea name=text rows=10 cols=65 wrap=soft id=InputFieldsText>'.$text.'</textarea><br /><br />');
$PHP_OUTPUT.=create_submit('Enter the article');
$PHP_OUTPUT.=('</form>');

?>