<?php

$db->query('UPDATE version
			SET went_live = ' . time() . '
			WHERE version_id = ' . $var['version_id']
		   );

forward(create_container('skeleton.php', 'changelog.php'));

?>