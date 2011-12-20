<?php

//////////////////////////////////////////////////
//
//	Script:		shop_gadget.php
//	Purpose:	Gadget Buying
//
//////////////////////////////////////////////////

if (!in_array($var['loc_id'], $SECTOR[$THIS_PLAYER->getSectorID()]['Locations'])) {
	$error .= get_status_message('red','Error','That shop does not exist in this sector!');
	include($GAME_FILES . '/error.php');
	return;
}

//get rev_gad
$rev_gad = array();
foreach ($GADGETS as $gad_name => $gad_arr) $rev_gad[$gad_arr['ID']] = $gad_name;

print_title('Browsing Gadgets');
$PHP_OUTPUT.= '<br />';
print_error($error);

$this_shop_sells = $SHOP_SELLS_GADGET[$var['loc_id']];
$pre_size = sizeof($this_shop_sells);
if (is_array($SHOP_SELLS_GADGET_SPECIAL[$var['loc_id']])) {
	foreach ($SHOP_SELLS_GADGET_SPECIAL[$var['loc_id']] as $gad_id => $require_array) {
		
		if (checkHardwareRequirements($require_array,$THIS_SECTOR,$THIS_PLAYER,$THIS_SHIP))
			$this_shop_sells[] = $gad_id;
	}
}
if ($pre_size < sizeof($this_shop_sells)) $special_stock = TRUE;
else $special_stock = FALSE;

if (sizeof($this_shop_sells) > 0) {
	if ($special_stock) $PHP_OUTPUT.= 'It looks like you have access to some of our special stock.<br />';
	$PHP_OUTPUT.= '<table class="standard"><tr><th>Name</th><th>Cost</th><th>Action</th></tr>';
	foreach ($this_shop_sells as $gad_id) {
		$PHP_OUTPUT.= '<tr><td>'.$rev_gad[$gad_id].'</td><td>'.number_format($GADGETS[$rev_gad[$gad_id]]['Cost']).'</td><td>';
		if ($THIS_PLAYER->getGadget($gad_id)!==false) $PHP_OUTPUT.= 'Already Owned';
		else {
			$link = array();
			$link['body'] = 'shop_gadget.php';
			$link['processing'] = 'shop_gadget_processing.php';
			$link['text'] = 'Buy';
			$link['loc_id'] = $var['loc_id'];
			$link['valid_for'] = -4;
			$link['gad_id'] = $gad_id;
			create_button($link, $id);
		}
		$PHP_OUTPUT.= '</td></tr>';
	}
	$PHP_OUTPUT.= '</table>';
}
else {
	$PHP_OUTPUT.= 'We\'ve got nothing for you here! Get outta here!<br />';
}

?>