<?

$names = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16);
shuffle($names);
$cur = array_pop($names);
$PHP_OUTPUT.=('.$db->escapeString($cur<br>');
shuffle($names);
$cur = array_pop($names);
$PHP_OUTPUT.=('.$db->escapeString($cur<br>');
shuffle($names);
$cur = array_pop($names);
$PHP_OUTPUT.=('.$db->escapeString($cur<br>');

?>