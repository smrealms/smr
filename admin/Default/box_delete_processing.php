<?
if (!isset($_REQUEST['message_id']))
   create_error('You must choose the messages you want to delete.');

foreach ($_REQUEST['message_id'] as $id)
    $db->query('DELETE FROM message_boxes WHERE message_id = '.$id);

forward(create_container('skeleton.php', 'notify_view.php'));
?>