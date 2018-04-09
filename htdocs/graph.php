<?php

$val = $_REQUEST['val'];
if (empty($val) || $val == '') $val = 3;
if ($val == 3) {
	
	$util = 3;
	$trade = 5;
	$combat = 10;
	$hunt = 8;
	
}
echo ('<body background="images/graph'.$val.'.gif">');
/*
echo ('<font color=white size=4><div align=center valign=top><font size=1>&nbsp;&nbsp;&nbsp;</font>'.$trade.'</div>');
echo ('<br /><br /><br /><br /><br /><br /><br /><font size=1><br /><br /><br /></font><div align=justify>'.$combat.'&nbsp;');
echo ('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
echo ('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
echo ('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
echo ('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
echo ('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
echo ('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
echo ('&nbsp;&nbsp;&nbsp;&nbsp;');
echo ('.$db->escapeString($hunt</div>');
echo ('<br /><br /><br /><br /><br /><br /><br /><br /><font size=1><br /></font><div align=center><font size=1>&nbsp;&nbsp;&nbsp;</font>'.$util.'</div></font>');


echo ('<font color="white" size="4">');
echo ('Trade - '.$trade.'<br />');
echo ('Combat Strength - '.$combat.'<br />');
echo ('Hunting - '.$hunt.'<br />');
echo ('Utility - '.$util.'<br />');
echo ('</font>');

echo ('<table class="standard">');
echo ('<tr><th align="center">Trait</th><th align="center">Rating</th></tr>');
echo ('<tr><td align="center">Trade - '.$trade.'</td></tr>');
echo ('<tr><td align="center">Combat Strength - '.$combat.'</td></tr>');
echo ('<tr><td align="center">Hunting - '.$hunt.'</td></tr>');
echo ('<tr><td align="center">Utility - '.$util.'</td></tr>');
echo ('</table>');*/
echo ('</body>');
