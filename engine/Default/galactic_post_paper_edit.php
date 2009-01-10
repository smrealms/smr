<?

$db2 = new SMR_DB();
$db->query('SELECT * FROM galactic_post_paper WHERE paper_id = '.$var['id'].' AND game_id = '.$player->getGameID());
$db->next_record();
$paper_title = stripslashes($db->f('title'));
$db->query('SELECT * FROM galactic_post_paper_content WHERE paper_id = '.$var['id']);
include(ENGINE . 'global/menue.inc');
$PHP_OUTPUT.=create_galactic_post_menue();
$PHP_OUTPUT.=($paper_title.'<br /><br /><ul>');
while ($db->next_record()) {

    $db2->query('SELECT * FROM galactic_post_article WHERE game_id = '.$player->getGameID().' AND article_id = ' . $db->f('article_id'));
    $db2->next_record();
    $article_title = stripslashes($db2->f('title'));
    $article_text = stripslashes($db2->f('text'));
    $PHP_OUTPUT.=('<li>'.$article_title.'<br /><li>'.$article_text.'<br /></li>');
    $container = array();
    $container['url'] = 'galactic_post_paper_edit_processing.php';
    $container['article_id'] = $db->f('article_id');
    transfer('id');
    $PHP_OUTPUT.=create_link($container, 'Remove this article from '.$paper_title.'</li>');
    $PHP_OUTPUT.=('<br /><br />');

}
$PHP_OUTPUT.=('</ul>');
if (!$db->nf())
    $PHP_OUTPUT.=('This paper has no articles');

?>