<?php

$db->query('UPDATE open_forms SET open = ' . $db->escapeBoolean(!$var['is_open']) . ' WHERE type=' . $db->escapeString($var['type']));

forward(create_container('skeleton.php', 'form_open.php'));
