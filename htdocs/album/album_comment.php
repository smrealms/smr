<?

function create_error_offline($msg) {
	header('Location: '.URL.'/error.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
	exit;

}


require_once('../config.inc');
require_once(ENGINE . 'Default/smr.inc');
require_once(LIB . 'Default/SmrMySqlDatabase.class.inc');
require_once(get_file_loc('SmrSession.class.inc'));

require_once(LIB . 'Album/album_functions.php');

if (SmrSession::$account_id == 0)
	$PHP_OUTPUT.=create_error_offline('You need to logged in to post comments!');

if (!isset($_GET['album_id']) || empty($_GET['album_id']))
	$PHP_OUTPUT.=create_error_offline('Which picture do you want comment?');
else
	$album_id = $_GET['album_id'];

if (!is_numeric($album_id))
	$PHP_OUTPUT.=create_error_offline('Picture ID has to be numeric!');

if ($album_id < 1)
	$PHP_OUTPUT.=create_error_offline('Picture ID has to be positive!');

if (isset($_GET['action']) && $_GET['action'] == 'Moderate') {

	$container = create_container('skeleton.php', 'album_moderate.php');
	$container['account_id'] = $album_id;

	forward($container);
	exit;

}

$db = new SmrMySqlDatabase();

if (!isset($_GET['comment']) || empty($_GET['comment']))
	$PHP_OUTPUT.=create_error_offline('Please enter a comment');
else
	$comment = mysql_escape_string($_GET['comment']);

// get current time
$curr_time = TIME;

// check if we have comments for this album already
$db->lockTable('album_has_comments');

$db->query('SELECT MAX(comment_id) FROM album_has_comments WHERE album_id = '.$album_id);
if ($db->nextRecord())
	$comment_id = $db->getField('MAX(comment_id)') + 1;
else
	$comment_id = 1;

$db->query('INSERT INTO album_has_comments
			(album_id, comment_id, time, post_id, msg)
			VALUES ('.$album_id.', '.$comment_id.', '.$curr_time.', '.SmrSession::$account_id.', '.$db->escapeString($comment).')');
$db->unlock();

header('Location: '.URL.'/album/?' . get_album_nick($album_id));
exit;

?>