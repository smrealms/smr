<?

$db2 = new SMR_DB();
$db3 = new SMR_DB();
if (isset($var['article'])) {

    $db->query('SELECT * FROM galactic_post_article WHERE article_id = '.$var['id'].' AND game_id = '.$player->getGameID());
    $db->next_record();
    $title = $db->f('title');
    $PHP_OUTPUT.=('Are you sure you want to delete the article named '.$title.'?');
    $container = array();
    $container['url'] = 'galactic_post_delete.php';
    transfer('article');
    transfer('id');
    $PHP_OUTPUT.=create_echo_form($container);
    $PHP_OUTPUT.=create_submit('Yes');
    $PHP_OUTPUT.=('</form>');
    $container = array();
    $container['url'] = 'skeleton.php';
    $container['body'] = 'galactic_post_view_article.php';
    transfer('id');
    $PHP_OUTPUT.=create_echo_form($container);
    $PHP_OUTPUT.=create_submit('No');
    $PHP_OUTPUT.=('</form>');

} else {

    $db->query('SELECT * FROM galactic_post_paper WHERE game_id = '.$player->getGameID().' AND paper_id = '.$var['id']);
    $db->next_record();
    $title = $db->f('title');
    $PHP_OUTPUT.=('Are you sure you want to delete the paper titled '.$title.' and the following articles with it<br><br>');
    $db2->query('SELECT * FROM galactic_post_paper_content WHERE game_id = '.$player->getGameID().' AND paper_id = '.$var['id']);
    while($db2->next_record()) {

        $article_id = $db2->f('article_id');
        $db3->query('SELECT * FROM galactic_post_article WHERE game_id = '.$player->getGameID().' AND article_id = '.$article_id);
        $db3->next_record();
        $article_title = stripslashes($db3->f('title'));
        $PHP_OUTPUT.=($article_title.'<br>');

    }
    $PHP_OUTPUT.=('<br>');

    $container = array();
    $container['url'] = 'galactic_post_delete.php';
    transfer('paper');
    transfer('id');
    $PHP_OUTPUT.=create_echo_form($container);
    $PHP_OUTPUT.=create_submit('Yes');
    $PHP_OUTPUT.=('</form>');
    $container = array();
    $container['url'] = 'skeleton.php';
    $container['body'] = 'galactic_post_view_article.php';
    transfer('id');
    $PHP_OUTPUT.=create_echo_form($container);
    $PHP_OUTPUT.=create_submit('No');
    $PHP_OUTPUT.=('</form>');

}

?>