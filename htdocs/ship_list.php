<?php
try {
	require_once('config.inc');

	$template = new Template();

	$seq = @$_REQUEST['seq'];
	$order = @$_REQUEST['order'];
	$hardwarea = @$_REQUEST['hardwarea'];

	$db = new SmrMySqlDatabase();
	$template->assign('class', buildSelector($db, 'class', 'ship_class_name', 'ship_class'));
	$template->assign('race', buildSelector($db, 'race', 'race_name', 'race', 'race_id'));
	$template->assign('speed', buildSelector($db, 'speed', 'speed', 'ship_type'));
	$template->assign('hardpoint', buildSelector($db, 'hp', 'hardpoint', 'ship_type'));
	$template->assign('restrict', buildRestriction());
	$template->assign('scanner', buildToggle('scannerPick'));
	$template->assign('cloak', buildToggle('cloakPick'));
	$template->assign('illusion', buildToggle('illusionPick'));
	$template->assign('jump', buildToggle('jumpPick'));
	$template->assign('scramble', buildToggle('scramblePick'));

	if (empty($seq)) {
		$seq = 'ASC';
	}
	elseif ($seq == 'ASC') {
		$seq = 'DESC';
	}
	else {
		$seq = 'ASC';
	}	
	$template->assign('seq', $seq);

	$allowedOrders = array('ship_name','race_name','cost','speed','hardpoint','buyer_restriction','lvl_needed','ship_class_name');

	if (!empty($order) && in_array($order,$allowedOrders)) {
		$order_by = $order .' '. $seq;
	}
	else {
		$order_by = 'ship_type.ship_type_id';
	}
	$order_by .= ', ship_name ASC, ship_type_support_hardware.hardware_type_id ASC';

	if(!empty($hardwarea) && is_numeric($hardwarea) && $hardwarea >=1 && $hardwarea <= 11) {
		$db->query('SELECT ship_type_id
					FROM ship_type_support_hardware
					WHERE hardware_type_id = '.$db->escapeNumber($hardwarea).'
					ORDER BY max_amount '.$seq);
		$db2 = new SmrMySqlDatabase();
		while ($db->nextRecord()) {
			$db2->query('SELECT *
						FROM ship_type
						JOIN ship_type_support_hardware USING(ship_type_id)
						JOIN ship_class USING(ship_class_id)
						JOIN race USING(race_id)
						WHERE ship_type_id=' . $db->escapeNumber($db->getInt('ship_type_id')) . '
						ORDER BY hardware_type_id ASC');
			if($db2->nextRecord()) {
				$shipArray[] = buildShipStats($db2);
			}
		}
	}
	else {
		$db->query('SELECT *
					FROM ship_type
					JOIN ship_type_support_hardware USING(ship_type_id)
					JOIN ship_class USING(ship_class_id)
					JOIN race USING(race_id)
					ORDER BY '.$order_by);
		while ($db->nextRecord()) {
			$shipArray[] = buildShipStats($db);
		}
	}
	$template->assign('shipArray', $shipArray);

	$template->display('ship_list.php');
}
catch(Throwable $e) {
	handleException($e);
}

function buildSelector($db, $id, $name, $table, $typeField = false) {
	$selector = '<br><select id="'.$id.'Pick" name="'.$name.'" onchange="'.$id.'Pickf()"><option value="All">All</option>';
	$db->query('
		SELECT DISTINCT '.$name. ($typeField!==false?',' . $typeField: '') . '
		FROM '.$table.'
		ORDER BY '.$name);
	$class = '';
	while ($db->nextRecord()) {
		if($typeField !== false) {
			$class = 'class="' . $id . $db->getInt($typeField) . '"';
		}
		$selector .= '<option '.$class.' value="'.$db->getField($name).'">'
			.$db->getField($name).'</option>';
	}
	$selector .= '</select>';
	return $selector;
}

function buildRestriction() {
	$restrict = '<br><select id="restrictPick" name="restrict" onchange="restrictPickf()">'
	.'<option value="All">All</option>'
	.'<option value="">None</option>'
	.'<option class="dgreen" value="Good">Good</option>'
	.'<option class="red" value="Evil">Evil</option></select>';
	
	return $restrict;

}

function buildToggle($id) {
	$toggle = '<br><select id="'.$id.'" name="'.$id.'" onchange="'.$id.'f()">'
	.'<option value="All">All</option>'
	.'<option value="Yes">Yes</option>'
	.'<option value="">No</option></select>';
	
	return $toggle;

}

function buildShipStats($db) {
	//we want to put them all in an array so we dont have to have 15 td rows
	$stat = array();
	$stat[] = str_replace(' ','&nbsp;',$db->getField('ship_name'));
	//$stat[] = str_replace(' ','&nbsp;',$db->getField('race_name'));
	$stat[] = array('race' . $db->getInt('race_id'), $db->getField('race_name'));
	$stat[] = str_replace(' ','&nbsp;',$db->getField('ship_class_name'));
	$stat[] = number_format($db->getInt('cost'));
	$stat[] = $db->getInt('speed');
	$stat[] = $db->getInt('hardpoint');
	if ($db->getField('buyer_restriction') == BUYER_RESTRICTION_GOOD)
		$restriction = '<span class="dgreen">Good</span>';
	elseif ($db->getField('buyer_restriction') == BUYER_RESTRICTION_EVIL)
		$restriction = '<span class="red">Evil</span>';
	else
		$restriction = '';
	$stat[] = $restriction;
//	$stat[] = $db->getInt('lvl_needed');
	$stat[] = number_format($db->getInt('max_amount'));
	$hardware_id = 2;
	//get our hardware
	while ($hardware_id <= 11)
	{
		if($db->nextRecord()) 
		{
			if ($hardware_id < 7)
				$stat[] = number_format($db->getInt('max_amount'));
			elseif ($db->getInt('max_amount') == 1)
				$stat[] = 'Yes';
			else
				$stat[] = '';
		}
		$hardware_id++;
	}
	return $stat;
}
