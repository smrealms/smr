<?
$notify_id = $_REQUEST['notify_id'];
if (!isset($notify_id))
   create_error('You must choose the messages you want to delete.');

foreach ($notify_id as $id)
    $db->query('DELETE FROM message_notify WHERE notify_id = '.$id);

forward(create_container('skeleton.php', 'notify_view.php'));
?>