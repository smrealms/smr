<?php

$bounties = 0;
$id = $var["id"];
$curr_player = new SMR_PLAYER($id, $player->game_id);
print_topic("Viewing $curr_player->player_name");
$db->query("SELECT * FROM bounty WHERE account_id = $id AND game_id = $player->game_id AND type = " . format_string(HQ, true));
while ($db->next_record()) {

    $claimer = $db->f("claimer_id");
    $time = time();
	//$days = ($time - $db->f("time")) / 60 / 60 / 24;
    //$amount = round($db->f("amount") * pow(1.05,$days));
    $amount = $db->f("amount");
    print("The <font color=green>Federal Government</font> is offering a bounty on $curr_player->player_name worth <font color=yellow>$amount</font> credits.<br>  ");
    if ($claimer != 0) {

        $claiming_player = new SMR_PLAYER($claimer, $player->game_id);
        print("This bounty can be claimed by $claiming_player->player_name<br>");

    }
    $bounties += 1;
	
}
if ($bounties > 0) print("<br><br><br>");


$db->query("SELECT * FROM bounty WHERE account_id = $id AND game_id = $player->game_id AND type = " . format_string(UG, true));

while ($db->next_record()) {

    $claimer = $db->f("claimer_id");
    $time = time();
	//$days = ($time - $db->f("time")) / 60 / 60 / 24;
    //$amount = round($db->f("amount") * pow(1.05,$days));
    $amount = $db->f("amount");
    print("The <font color=red>Underground</font> is offering a bounty on $curr_player->player_name worth <font color=yellow>$amount</font> credits.<br>");
    if ($claimer != 0) {

        $claiming_player = new SMR_PLAYER($claimer, $player->game_id);
        print("This bounty can be claimed by $claiming_player->player_name<br>");

    }
	$bounties += 1;
}

if ($bounties == 0)
	print("This player has no bounties<br>");
?>