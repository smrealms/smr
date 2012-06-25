<?php

//////////////////////////////////////////////////
//
//	Script:		shop_gadget_processing.php
//	Purpose:	Gadget Buying
//
//////////////////////////////////////////////////

//get rev_gad
$rev_gad = array();
foreach ($GADGETS as $gad_name => $gad_arr) $rev_gad[$gad_arr['ID']] = $gad_name;

if ($THIS_PLAYER->getCredits() < $GADGETS[$rev_gad[$var['gad_id']]]['Cost'] && !has_privilege('Money Doesn\'t Matter')) {
	$error .= get_status_message('red','Error','You do not have enough money!');
	return;
}

//take money and add gadget
query('BEGIN;');
if($THIS_PLAYER->addGadget($var['gad_id'])) {
	$THIS_PLAYER->decreaseCredits($GADGETS[$rev_gad[$var['gad_id']]]['Cost']);
	$error .= get_status_message('green','Success','We have obtained the gadget!');
}
query('COMMIT;');
?>