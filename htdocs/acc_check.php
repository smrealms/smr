<?php

include('config.inc');
require_once(LIB . 'global/smr_db.inc');
include(ENGINE . 'Old_School/smr.inc');
include(ENGINE . 'Old_School/help.inc');

if(
	(!isset($_POST['attacker']) || !is_numeric($_POST['attacker'])) ||
	(!isset($_POST['defender']) || !is_numeric($_POST['defender'])) ||
	(!isset($_POST['weapon']) || !is_numeric($_POST['weapon']))
) {
	$attacker = $defender = 0;
	$weapon = 1;
	$err = true;
}
else {
	$attacker = $_POST['attacker'];
	$defender = $_POST['defender'];
	$weapon = $_POST['weapon'];
	$err = false;
}

echo '<html><head><title>SMR - Accuracy checker</title></head><body>';

echo '<form id="FORM" method="POST" action="acc_check.php">';
echo 'Attacker level <input type="test" value="' . $attacker . '" name="attacker" maxlength="2"><br>';
echo 'Defender level <input type="test" value="' . $defender . '" name="defender" maxlength="2"><br>';

$db = new SMR_DB();

$db->query('SELECT weapon_name, weapon_type_id,accuracy FROM weapon_type');

echo 'Weapon <select name="weapon">';

while($db->next_record()){
	
	echo '<option value="' . $db->f('weapon_type_id') . '"';
	if($weapon == $db->f('weapon_type_id')) {
		echo 'selected';
	}
	echo '>' .  $db->f('weapon_name') . ' (' .$db->f('accuracy') . ')' . '</option>';
}

echo '</select><br>';
echo 'I need my junk output.<input type="checkbox" name="junk" value="1"';
if(isset($_POST['junk'])) echo 'checked';
echo '><br>';
echo '<input type="submit" value="Calculate">';
echo '</form>';

if(!$err){
	$db->query('SELECT weapon_name,accuracy FROM weapon_type WHERE weapon_type_id=' . (int)$_POST['weapon'] . ' LIMIT 1');
	if($db->next_record()) {
		echo 'Level ';
		echo $attacker;
		echo ' player attacks level ';
		echo $defender;
		echo ' player with ';
		echo $db->f('weapon_name') . '<br>';
		echo 'Base accuracy: ' . $db->f('accuracy') . '%<br>';
		$accuracy = round($db->f('accuracy') + $attacker - ($defender/2));
		echo 'Level adjusted accuracy: ' . $accuracy;
		$num_rands = 10000;
		$junk = '';
		$sum = 0;
		for($i=0;$i<$num_rands;++$i){
			$rand = (mt_rand(0,99) + (0.15*mt_rand(0,99))) / 1.15;
			if($rand<=$accuracy) {
				if(isset($_POST['junk'])) {
					$junk .= 'hit<br>';
				}
				++$sum;
			}
			else if(isset($_POST['junk'])) {
				$junk .= 'miss<br>';
			}
		}
		echo '%<br>New distribution adjusted accuracy (Approximate): ' . round(100 * $sum/$num_rands);
		$sum = 0;
		$num_rands = 10000;
		for($i=0;$i<$num_rands;++$i){
			$rand = (mt_rand(1,100) + mt_rand(1,100)) / 2;
			if($rand<=$accuracy) {
				++$sum;
			}
		}
		echo '%<br><br>';
	}
	else {
		echo 'Quit screwing around';
	}
}

echo 'Notes:<br>Linear distribution accuracy is the same as level adjusted accuracy.<br>New distribution adjusted accuracy may vary by 1%, this is due to using an iterative solution.<br />';

if(isset($junk)) echo $junk;

echo '</body></html>';


?>