<?

$type = $var['type'];
if ($type == 'alliance')
	foreach ($alliance as $value)
		$db->query('INSERT INTO mb_exceptions (type, value) VALUES (\'alliance\','.$db->escapeString($value).')');
else
	foreach ($personal as $value)
		$db->query('INSERT INTO mb_exceptions (type, value) VALUES (\'personal\','.$db->escapeString($value).')');
$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'keyword_search.php';
$container['msg'] = '<div align=center><font color=red><b>Added Exceptions</b></font></div>';
forward($container);
?>