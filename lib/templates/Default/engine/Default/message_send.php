<?php $this->includeTemplate('includes/menu.inc',array('MenuItems' => array(
					array('Link'=>$ViewMessagesLink,'Text'=>'View Messages'),
					array('Link'=>$SendCouncilMessageLink,'Text'=>'Send Council Message'),
					array('Link'=>$SendGlobalMessageLink,'Text'=>'Send Global Message'),
					array('Link'=>$ManageBlacklistLink,'Text'=>'Manage Blacklist'))));
$this->includeTemplate('includes/CommonMessageSend.inc'); ?>