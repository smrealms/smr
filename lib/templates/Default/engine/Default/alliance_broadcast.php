<?php $this->includeTemplate('includes/menu.inc',array('MenuItems' => array(
					array('Link'=>@$MotdLink,'Text'=>'Message of Day'),
					array('Link'=>$RosterLink,'Text'=>'Roster'),
					array('Link'=>@$AllianceMessageLink,'Text'=>'Send Message'),
					array('Link'=>@$MessageBoardLink,'Text'=>'Message Board'),
					array('Link'=>@$AlliancePlanetsLink,'Text'=>'Planets'),
					array('Link'=>@$AllianceForcesLink,'Text'=>'Forces'),
					array('Link'=>@$AllianceOptionsLink,'Text'=>'Options'),
					array('Link'=>$ListAlliancesLink,'Text'=>'List Alliances'),
					array('Link'=>$ViewAllianceNewsLink,'Text'=>'View News'))));
$this->includeTemplate('includes/CommonMessageSend.inc'); ?>