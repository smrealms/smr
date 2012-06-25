<?php

//////////////////////////////////////////////////
//
//	Script:		ship_upgrade.php
//	Purpose:	Assign Empty Ship Points
//
//////////////////////////////////////////////////

$PHP_OUTPUT.= '<h1>Upgrade Ship</h1><br />';
$PHP_OUTPUT.= '<table class="nobord">';
$PHP_OUTPUT.= '<tr>';
$PHP_OUTPUT.= '<td>Total Points</td>';
$PHP_OUTPUT.= '<td>' . $ship->getTotalUpgradeSpace() . '</td>';

$PHP_OUTPUT.= '</tr><tr>';

$PHP_OUTPUT.= '<td>Used Points</td>';
$PHP_OUTPUT.= '<td>' . $ship->getUsedUpgradeSpace() . '</td>';

$PHP_OUTPUT.= '</tr><tr>';

$PHP_OUTPUT.= '<td>Points Remaining</td>';
$PHP_OUTPUT.= '<td>' . $ship->getFreeUpgradeSpace() . '</td>';

$PHP_OUTPUT.= '</tr><tr>';

$PHP_OUTPUT.= '<td>Next Point Removal Available</td>';
$PHP_OUTPUT.= '<td>';
if (!$ship->canDowngrade()) {
	$remove = FALSE;
	$time_left = $ship->getRemainingDowngradeWait();
	$PHP_OUTPUT.= '<span class="red">' . format_time($time_left) . '</span>';
}
else {
	$remove = TRUE;
	$PHP_OUTPUT.= '<span class="green">Now</span>';
}
$PHP_OUTPUT.= '</td>';
$PHP_OUTPUT.= '</tr></table>';
if (isset($error)) $PHP_OUTPUT.= $error . '<br />';
$PHP_OUTPUT.= '<table class="standard">';
$PHP_OUTPUT.= '<tr><th>Upgrade Area</th><th>Current Upgrades</th><th>Point Cost</th><th>Action</th></tr>';

foreach ($STAT_INCREASE_TYPES as $upgrade_id => $upgrade_array) {
	$baseAmountOfAttribute=getBaseAmountOfUpgradeAttribute($ship,$upgrade_array);
	
	$PHP_OUTPUT.= '<tr>';
	$PHP_OUTPUT.= '<td>Add ' .
		($upgrade_array['Static'] !== false ? '+'.$upgrade_array['Static'] . ' static' : '') .
		($upgrade_array['Static'] !== false && $upgrade_array['Percent'] !== false ? ' and ' : '') .
		($upgrade_array['Percent'] !== false ? '+' . $upgrade_array['Percent'] . '%' : '') .
		' to ' . $upgrade_array['Name'] . 
		($upgrade_array['Percent'] !== false ? ' for a total of +' . floor($upgrade_array['Static'] + $baseAmountOfAttribute*$upgrade_array['Percent']/100) : '') .
		'</td>';
	$current = (isset($ship['Upgrades'][$upgrade_id]) ? $ship['Upgrades'][$upgrade_id] : 0);
	$PHP_OUTPUT.= '<td class="center">' . $current . '</td>';
	$cost = getModifiedUpgradeCost($ship,$upgrade_id);
	$PHP_OUTPUT.= '<td class="center">' . $cost . '</td>';
	
	$link = array();
	$link['body'] = 'ship_upgrade.php';
	$link['processing'] = 'ship_upgrade_processing.php';
	$link['upgrade_id'] = $upgrade_id;
	print_form($link,$id);
	
	$PHP_OUTPUT.= '<td class="center">';
	$PHP_OUTPUT.= '<input type="submit" value="Add" name="submit">';
	if ($remove)
		$PHP_OUTPUT.= '<br /><input type="submit" value="Remove" name="submit">';
	$PHP_OUTPUT.= '</td>';
	$PHP_OUTPUT.= '</form>';
	$PHP_OUTPUT.= '</tr>';
}

$PHP_OUTPUT.= '</table>';

?>