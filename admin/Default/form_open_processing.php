<?php

if ($var['open'])
	$db->query('UPDATE open_forms SET open = \'FALSE\' WHERE type='.$db->escapeString($var['type']));
else
	$db->query('UPDATE open_forms SET open = \'TRUE\' WHERE type='.$db->escapeString($var['type']));

forward(create_container('skeleton.php', 'form_open.php'));
