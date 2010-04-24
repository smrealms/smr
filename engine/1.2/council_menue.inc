<?php

//council_menue.inc
if (onCouncil($race_id))
	$menue_items[] = create_link(create_container("skeleton.php", "council_vote.php"),
												  "Voting Center");

if ($president->account_id == $player->account_id)
	$menue_items[] = create_link(create_container("skeleton.php", "council_embassy.php"),
												  "Embassy");

?>