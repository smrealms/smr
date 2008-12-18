<?

$val = $_REQUEST['val'];
if (empty($val) || $val == '') $val = 3;
if ($val == 3) {
	
	$util = 3;
	$trade = 5;
	$combat = 10;
	$hunt = 8;
	
}
$PHP_OUTPUT.=('<body background="http://beta.smrealms.de/images/graph$val.gif">');
/*
$PHP_OUTPUT.=('<font color=white size=4><div align=center valign=top><font size=1>&nbsp;&nbsp;&nbsp;</font>$trade</div>');
$PHP_OUTPUT.=('<br><br><br><br><br><br><br><font size=1><br><br><br></font><div align=justify>$combat&nbsp;');
$PHP_OUTPUT.=('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
$PHP_OUTPUT.=('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
$PHP_OUTPUT.=('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
$PHP_OUTPUT.=('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
$PHP_OUTPUT.=('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
$PHP_OUTPUT.=('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
$PHP_OUTPUT.=('&nbsp;&nbsp;&nbsp;&nbsp;');
$PHP_OUTPUT.=('.$db->escapeString($hunt</div>');
$PHP_OUTPUT.=('<br><br><br><br><br><br><br><br><font size=1><br></font><div align=center><font size=1>&nbsp;&nbsp;&nbsp;</font>$util</div></font>');


$PHP_OUTPUT.=('<font color="white" size="4">');
$PHP_OUTPUT.=('Trade - $trade<br>');
$PHP_OUTPUT.=('Combat Strength - $combat<br>');
$PHP_OUTPUT.=('Hunting - $hunt<br>');
$PHP_OUTPUT.=('Utility - $util<br>');
$PHP_OUTPUT.=('</font>');

$PHP_OUTPUT.=('<table border="0" class="standard" cellspacing="0">');
$PHP_OUTPUT.=('<tr><th align="center">Trait</th><th align="center">Rating</th></tr>');
$PHP_OUTPUT.=('<tr><td align="center">Trade - $trade</td></tr>');
$PHP_OUTPUT.=('<tr><td align="center">Combat Strength - $combat</td></tr>');
$PHP_OUTPUT.=('<tr><td align="center">Hunting - $hunt</td></tr>');
$PHP_OUTPUT.=('<tr><td align="center">Utility - $util</td></tr>');
$PHP_OUTPUT.=('</table>');*/
$PHP_OUTPUT.=('</body>');

?>