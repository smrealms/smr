<?

$time = time();

$PHP_OUTPUT.=('<div align=center>Current Times for Admins<br><table border=1><tr><th align=center>Admin</th><th align=center>Time</th></tr>');
$spock = $time;
//offset hours for v
$offset = 8 * 60 * 60;
$vtreka = $time + $offset;
$PHP_OUTPUT.=('<tr><td align=center>Vtreka</td><td align=center nowrap>' . date ('m/j/Y g:i:s A', $vtreka) . '.</td></tr>');
$PHP_OUTPUT.=('<tr><td align=center>MrSpock</td><td align=center nowrap>' . date ('m/j/Y g:i:s A', $spock) . '.</td></tr>');
$blum = $time;
$PHP_OUTPUT.=('<tr><td align=center>Blum</td><td align=center nowrap>' . date ('m/j/Y g:i:s A', $blum) . '.</td></tr>');
//offset hours for roa
$offset = 1 * 60 * 60;
$roach = $time - $offset;
$PHP_OUTPUT.=('<tr><td align=center>Roach</td><td align=center nowrap>' . date ('m/j/Y g:i:s A', $roach) . '.</td></tr>');
//offset hours for du
$offset = 6 * 60 * 60;
$dude = $time - $offset;
$PHP_OUTPUT.=('<tr><td align=center>Dudeman</td><td align=center nowrap>' . date ('m/j/Y g:i:s A', $dude) . '.</td></tr>');
//offset hours for na
$offset = 6 * 60 * 60;
$nariis = $time - $offset;
$PHP_OUTPUT.=('<tr><td align=center>Nariis</td><td align=center nowrap>' . date ('m/j/Y g:i:s A', $nariis) . '.</td></tr>');
//offset hours for im
$offset = 6 * 60 * 60;
$immy = $time - $offset;
$PHP_OUTPUT.=('<tr><td align=center>Imhotep</td><td align=center nowrap>' . date ('m/j/Y g:i:s A', $immy) . '.</td></tr>');
//offset hours for az
$offset = 8 * 60 * 60;
$azool = $time - $offset;
$PHP_OUTPUT.=('<tr><td align=center>Azool</td><td align=center nowrap>' . date ('m/j/Y g:i:s A', $azool) . '.</td></tr>');
//offset hours for dar
$offset = 9 * 60 * 60;
$darth = $time - $offset;
$PHP_OUTPUT.=('<tr><td align=center>Darth Maul</td><td align=center nowrap>' . date ('m/j/Y g:i:s A', $darth) . '.</td></tr>');
//offset hours for pv
$offset = 11 * 60 * 60;
$pv = $time - $offset;
$PHP_OUTPUT.=('<tr><td align=center>Prince Valiant</td><td align=center nowrap>' . date ('m/j/Y g:i:s A', $pv) . '.</td></tr>');

$PHP_OUTPUT.=('</table>');

$PHP_OUTPUT.=('<br>And for those of you that don\'t understand AM and PM<br><table border=1><tr><th align=center>Admin</th><th align=center>Time</th></tr>');
$PHP_OUTPUT.=('<tr><td align=center>Vtreka</td><td align=center nowrap>' . date ('m/j/Y H:i:s', $vtreka) . '.</td></tr>');
$PHP_OUTPUT.=('<tr><td align=center>MrSpock</td><td align=center nowrap>' . date ('m/j/Y H:i:s', $spock) . '.</td></tr>');
$PHP_OUTPUT.=('<tr><td align=center>Blum</td><td align=center nowrap>' . date ('m/j/Y H:i:s', $blum) . '.</td></tr>');
$PHP_OUTPUT.=('<tr><td align=center>Roach</td><td align=center nowrap>' . date ('m/j/Y H:i:s', $roach) . '.</td></tr>');
$PHP_OUTPUT.=('<tr><td align=center>Dudeman</td><td align=center nowrap>' . date ('m/j/Y H:i:s', $dude) . '.</td></tr>');
$PHP_OUTPUT.=('<tr><td align=center>Nariis</td><td align=center nowrap>' . date ('m/j/Y H:i:s', $nariis) . '.</td></tr>');
$PHP_OUTPUT.=('<tr><td align=center>Imhotep</td><td align=center nowrap>' . date ('m/j/Y H:i:s', $immy) . '.</td></tr>');
$PHP_OUTPUT.=('<tr><td align=center>Azool</td><td align=center nowrap>' . date ('m/j/Y H:i:s', $azool) . '.</td></tr>');
$PHP_OUTPUT.=('<tr><td align=center>Darth Maul</td><td align=center nowrap>' . date ('m/j/Y H:i:s', $darth) . '.</td></tr>');
$PHP_OUTPUT.=('<tr><td align=center>Prince Valiant</td><td align=center nowrap>' . date ('m/j/Y H:i:s', $pv) . '.</td></tr>');

$PHP_OUTPUT.=('</table></div>');

?>