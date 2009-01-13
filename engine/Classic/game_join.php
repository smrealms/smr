<?
// TODO: Needs reworking with the new CSS
$db->query("SELECT * FROM game WHERE game_id = " . $var["game_id"]);
if ($db->next_record()) {

    $start_date = $db->f("start_date");
    $end_date = $db->f("end_date");
    $max_players = $db->f("max_players");
    $type = $db->f("game_type");
    $needed = $db->f("credits_needed");

}

// do we need credits for this game?
if ($needed > 0) {

	// find how many credits they have.
	$db->query("SELECT * FROM account_has_credits WHERE account_id = $account->account_id");
	if ($db->next_record())
	    $have = $db->f("credits_left");
	else
	    $have = 0;

	// do we have enough
	if ($have < $needed) {

	    create_error("Sorry you dont have enough SMR Credits to play this game.<br>To get SMR credits you need to donate to SMR");
	    return;

	}

}

// is the game already full?
$db->query("SELECT * FROM player WHERE game_id = " . $var["game_id"]);
if ($db->nf() >= $max_players) {

    create_error("The maximum number of players in that game is reached!");
    return;

}

if (date("Y-m-d") < $start_date) {

    create_error("You want to join a game that hasn't started yet?");
    return;

}

if (date("Y-m-d") > $end_date) {

    create_error("You want to join a game that is already over?");
    return;

}

print_topic("JOIN GAME");

print("<script language=\"Javascript\">");
print("function go()\n");
print("{\n");
print("desc = new Array(");
$db->query("SELECT * FROM race");

while ($db->next_record())
    if (!$first) {

        print("\"" . $db->f("race_description") . "\"");
        $first = 1;

    } else
        print(", \"" . $db->f("race_description") . "\"");

print(");\n");
print("var race_id = document.forms[0].race_id.options[document.forms[0].race_id.selectedIndex].value;\n");
print("document.race_image.src = \"images/race\" + race_id + \".gif\";\n");
print("document.getElementById('graphframe').src = \"images/graph\" + race_id + \".gif\";\n");
print("document.FORM.race_descr.value = desc[race_id - 1]; }\n");

print("</script>");

print("<p>");
print_table();
print("<tr>");
print("<th width=\"150\">Game</th>");
print("<th>Start Date</th>");
print("<th>Max Players</th>");
print("<th>Type</th>");
print("</tr>");

$db->query("SELECT * FROM game WHERE game_id = " . $var["game_id"]);
if ($db->next_record()) {

    $game_name = $db->f("game_name");
    $game_description = $db->f("game_description");
    $start_date = $db->f("start_date");
    $max_players = $db->f("max_players");
    $game_type = $db->f("game_type");

}

print("<tr>");
print("<td>$game_name (" . $var["game_id"] . ")</td>");
print("<td align=\"center\">$start_date</td>");
print("<td align=\"center\">$max_players</td>");
print("<td align=\"center\">$game_type</td>");
print("</tr>");
print("</table></p>");

print("<p>$game_description</p>");

// create a container that will hold next url and additional variables.
$container = array();
$container["game_id"] = $var["game_id"];
$container["url"] = "game_join_processing.php";
print_form($container);

print("<p>");
echo '<h1>Create Merchant</h1><br />';
echo '<table cellspacing="0" cellpadding="0" class="nobord nohpad">';
print("<tr><td>");

print("<span>Each Space Merchant Realms game requires you to create a new Merchant. For this purpose you must choose a name and a race.<br>");
print("To enhance the roleplaying atmosphere of the game, there are certain criteria your name must meet.<br>");
print("The following names will not be accepted:");
print("<ul>");
print("<li>Names with references to \"out of character\" information - ie. something that would make sense only to the player, not the character - such as \"SpaceGamer\", \"SMR Rules\" etc.</li>");
print("<li>Names that are taken from real life history, or the names of existing people - eg. \"Osama bin Laden\", \"Stalin\" or \"Harrison Ford\".</li>");
print("<li>Names that convey an attitude towards yourself or someone else - such as \"Lamer\" or \"Shadow Sucks\".</li>");
print("<li>Names that make excessive use of special characters, eg. \"~-=[Daron]=-~\" should be \"Daron\" instead.</li>");
print("<li>Names that look similar or identical to another player in an attempt to trick other players are prohibited.</li>");
print("</ul>");
print("If you disregard these rules, your player will be deleted, so choose your name wisely.</span><br><br>");

print("<table border=\"0\" cellpadding=\"3\">");
print("<tr>");
print("<td align=\"right\"><b>Name:</b></td>");
print("<td><input type=\"text\" name=\"player_name\" maxlength=\"32\" id=\"InputFields\"></td>");
print("<td rowspan=\"4\" class=\"standard\"><img name=\"race_image\" src=\"images/race1.gif\"></td>");
print("</tr>");

print("<tr>");
print("<td align=\"right\"><b>Race:</b></td>");
print("<td>");
print("<select name=\"race_id\" size=\"1\" style=\"border-width:0px;width:150px;\" OnChange=\"go();\">");
print("<option value=\"1\">[please select]</option>");

$db2 = new SmrMySqlDatabase();
//this prevents multiple races appearing when there is more than 1 game
$only = array();
// get all available hq's
$db->query("SELECT location_name, location.location_type_id as loc_id
			FROM location NATURAL JOIN location_type
			WHERE location.location_type_id > ".UNDERGROUND." AND
				  location.location_type_id < ".FED." AND
				  game_id = " . $var["game_id"] . "
			ORDER BY location.location_type_id");
while ($db->next_record()) {

	// get the name for this race
	// HACK! cut ' HQ' from location name!
	$race_name = substr(stripslashes($db->f("location_name")), 0, -3);

	$curr_race_id = $db->f("loc_id") - 101;
	if (in_array($curr_race_id, $only)) continue;
	$only[] = $curr_race_id;
	// get number of traders in game
	$db2->query("SELECT * FROM player WHERE race_id = $curr_race_id AND game_id = " . $var["game_id"]);

	$race_name .= " (" . $db2->nf() . " Trader)";

    print("<option value=\"$curr_race_id\"");
    if ($race_id == $curr_race_id)
    	print(" selected");

    print(">$race_name</option>");

}

print("</select>");
print("</td>");
print("</tr>");

print("<tr>");
print("<td align=\"right\">&nbsp;</td>");
print("<td>");
print_submit("Create Player");
print("</td>");
print("</tr>");

print("<tr>");
print("<td colspan=\"2\">");
print("<textarea name=\"race_descr\" id=\"InputFields\" style=\"width:300px;height:275px;border:0;\"></textarea>");
print("</td>");
print("</tr>");

print("</table>");

print("</td></tr>");

print("<tr><td align=center><table><tr><td align=center colspan=4 class=\"center\">Trading</td></tr><tr><td align=left>Combat<br>Strength</td><td align=center colspan=2>");
print("<img width=\"440\" height=\"440\" border=\"0\" name=\"graph\" id=\"graphframe\" src=\"images/graph1.gif\"/>");
print("</td><td align=right>Hunting</td></tr><tr><td align=center colspan=4 class=\"center\">Utility</td></tr></table></td></tr></table>");

print("</p>");

print("</form>");

?>