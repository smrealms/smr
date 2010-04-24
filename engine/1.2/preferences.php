<?php

print_topic("PREFERENCES");

if (isset($var["reason"]))
	print("<p><big><b style=\"color:red;\">" . $var["reason"] . "</b></big></p>");

//find how many credits they have
$db->query("SELECT * FROM account_has_credits WHERE account_id = $account->account_id");
if ($db->next_record())
	$have = $db->f("credits_left");
else
	$have = 0;

print("<p>");
print_form(create_container("preferences_processing.php", ""));
print("<table cellpadding=\"5\">");

print("<tr>");
print("<td>Login:</td>");
print("<td><b>$account->login</b></td>");
print("</tr>");

print("<tr>");
print("<td>ID:</td>");
print("<td>$account->account_id</td>");
print("</tr>");

print("<tr>");
print("<td>SMR&nbsp;Credits:</td>");
print("<td>$have</td>");
print("</tr>");

//ban points go here
$points = $account->getPoints();
print("<tr>");
print("<td>Ban Points:</td>");
print("<td>$points</td>");
print("</tr>");

print("<tr>");
print("<td>old password:</td>");
print("<td><input type=\"password\" name=\"old_password\" id=\"InputFields\" size=\"25\"></td>");
print("</tr>");

print("<tr>");
print("<td>new password:</td>");
print("<td><input type=\"password\" name=\"new_password\" id=\"InputFields\" size=\"25\"></td>");
print("</tr>");

print("<tr>");
print("<td>retype password:</td>");
print("<td><input type=\"password\" name=\"retype_password\" id=\"InputFields\" size=\"25\"></td>");
print("</tr>");

print("<tr>");
print("<td>&nbsp;</td>");
print("<td>");
print_submit("Change Password");
print("</td>");
print("</tr>");

print("<tr><td colspan=\"2\">&nbsp;</td></tr>");

print("<tr>");
print("<td>eMail:</td>");
print("<td><input type=\"text\" name=\"email\" value=\"$account->email\" id=\"InputFields\" size=\"50\"></td>");
print("</tr>");

print("<tr>");
print("<td>&nbsp;</td>");
print("<td>");
print_submit("Save and resend validation code");
print("</td>");
print("</tr>");

print("<tr><td colspan=\"2\">&nbsp;</td></tr>");

print("<tr><td>Hall of Fame Name:</td>");
print("<td><input type=\"text\" name=\"HoF_name\" value=\"$account->HoF_name\" id=\"InputFields\" size=\"50\"></td>");
print("</tr>");

print("<tr>");
print("<td>&nbsp;</td>");
print("<td>");
print_submit("Change Name");
print("</td>");
print("</tr>");

print("<tr><td colspan=\"2\">&nbsp;</td></tr>");

print("<tr><td>Timezone:</td>");
print("<td>");
$time = time();
//get current offset
$db->query("SELECT * FROM account WHERE account_id = ".SmrSession::$old_account_id);
$db->next_record();
$offset = $db->f("offset");
print("<select name=\"timez\" id=\"InputFields\">");
for ($i = -12; $i<= 11; $i++) {
	
	print("<option value=$i");
	if ($offset == $i) print(" selected");
	print(">" . date("g:i:s A", $time + $i * 3600));
	
}
print"</select>";
print("</td>");
print("</tr>");

print("<tr>");
print("<td>&nbsp;</td>");
print("<td>");
print_submit("Change Timezone");
print("</td>");
print("</tr>");
print("</form>");

print("<tr><td colspan=\"2\">&nbsp;</td></tr>");

print_form(create_container("skeleton.php", "preferences_confirm.php"));

print("<tr>");
print("<td>SMR Credits:</td>");
print("<td><input type=\"text\" name=\"amount\" id=\"InputFields\" style=\"width:30px;text-align:center;\">&nbsp;credits&nbsp;to&nbsp;");

if (SmrSession::$game_id > 0) {

	print("<select name=\"account_id\" id=\"InputFields\">");
	$db->query("SELECT * FROM player WHERE game_id = ".SmrSession::$game_id." ORDER BY player_name");
	while ($db->next_record())
		print("<option value=\"" . $db->f("account_id") . "\">" . stripslashes($db->f("player_name")) . " (" . $db->f("player_id") . ")</option>");

} else {

	print("the&nbsp;account&nbsp;of&nbsp;<select name=\"account_id\" id=\"InputFields\">");
	$db->query("SELECT * FROM account ORDER BY login");
	while ($db->next_record())
		print("<option value=\"" . $db->f("account_id") . "\">" . $db->f("login") . "</option>");

}

print("</select>");
print("</td>");
print("</tr>");

print("<tr><td>&nbsp;</td>");
print("<td>");
print_submit("Transfer");
print("</td>");
print("</tr>");
print("</form>");

print("<tr><td colspan=\"2\">&nbsp;</td></tr>");

print_form(create_container("preferences_processing.php", ""));

print("<tr>");
print("<td>Display Ship Images:</td>");
print("<td>Yes: <input type=\"radio\" name=\"images\" id=\"InputFields\" value=\"Yes\"");
if ($account->images == "Yes") print(" CHECKED");
print("><br>No: <input type=\"radio\" name=\"images\" id=\"InputFields\" value=\"No\"");
if ($account->images == "No") print(" CHECKED");
print(">");
print("</td>");
print("</tr>");

print("<tr><td>&nbsp;</td>");
print("<td>");
print_submit("Change");
print("</td>");
print("</tr>");

$container = array();
$container['url'] = 'preferences_processing.php';
$form = create_form($container, 'Change Size');

echo $form['form'];

echo '<tr><td>Font size</td><td>';
echo '<input type="text" size="4" name="fontsize" value="' . $account->fontsize . '">';
echo ' Minimum font size is 50%</td><tr><td>&nbsp;</td>';
echo '<td>';
echo $form['submit'];
echo '</td></tr>';
print("</form>");
print("</table>");
print("</p>");

echo '<h2>Account players</h2><br>';
$db->query('select game.game_id as game_id, game.game_name as game_name, player.player_name as player_name, player.name_changed as name_changed from player natural join game where player.account_id=' . SmrSession::$old_account_id . ' and game.enabled=true and game.end_date>\'' . date("Y-m-d") . '\'');
if($db->nf()) {
	echo '<table class="standard" cellspacing="0" cellpadding="0"><tr><th>Game</th><th>Name</th></tr>';
	while($db->next_record()) {
		$row = $db->fetch_row();

		echo '<tr><td>',$row['game_name'],'</td><td>';
		if($row['name_changed'] == 'false') {
			$container = array();
			$container['url'] = 'preferences_processing.php';
			$container['game_id'] = $row['game_id'];
			$form = create_form($container,'Alter Player');
			echo $form['form'];
	
			echo '<input type="text" maxlength="32" name="PlayerName" value="' . $row['player_name'] . '" size="32">&nbsp;&nbsp;';
			echo $form['submit'];
			echo '</form>';
		}
		else {
			echo $row['player_name'];
		}
		echo '</td></tr>';	
	}
	echo '</table>';
}
else {
	echo "There are no players registered with this account.";
}

?>
