<?

function create_error_offline($msg) {

	global $URL;

	header('Location: '.$URL.'/error.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
	exit;

}


include('../config.inc');
require_once(ENGINE . 'Old_School/smr.inc');
include($LIB . 'global/smr_db.inc');
require_once(get_file_loc('SmrSession.class.inc'));
include(ENGINE . 'Old_School/smr.inc');

include('album_functions.php');

// get session
$session = new SmrSession();

if (SmrSession::$account_id == 0)
	$PHP_OUTPUT.=create_error_offline('You need to logged in to post comments!');

if (empty($_GET['album_id']))
	$PHP_OUTPUT.=create_error_offline('Which picture do you want comment?');
else
	$album_id = $_GET['album_id'];

if (!is_numeric($album_id))
	$PHP_OUTPUT.=create_error_offline('Picture ID has to be numeric!');

if ($album_id < 1)
	$PHP_OUTPUT.=create_error_offline('Picture ID has to be positive!');

if ($_GET['action'] == 'Moderate') {

	$container = create_container('skeleton.php', 'album_moderate.php');
	$container['account_id'] = $album_id;

	forward($container);
	exit;

}

$db = new SMR_DB();

if (empty($_GET['comment']))
	$PHP_OUTPUT.=create_error_offline('Please enter a comment');
else
	$comment = mysql_escape_string($_GET['comment']);

// get current time
$curr_time = time();

// check if we have comments for this album already
$db->lock('album_has_comments');

$db->query('SELECT MAX(comment_id) FROM album_has_comments WHERE album_id = $album_id');
if ($db->next_record())
	$comment_id = $db->f('MAX(comment_id)') + 1;
else
	$comment_id = 1;

$db->query('INSERT INTO album_has_comments
			(album_id, comment_id, time, post_id, msg)
			VALUES ('.$album_id.', '.$comment_id.', '.$curr_time.', '.SmrSession::$account_id.', '.$db->escapeString($comment).')');
$db->unlock();

header('Location: '.$URL.'/album/?' . get_album_nick($album_id));
exit;

?>