<?php

if (different_level($rank_id, $curr_account->get_rank(), $account->veteran, $curr_account->veteran))
	$skip = TRUE;
else
	$skip = FALSE;
					
?>