<?
// Remove the lock if we're holding one (ie logged off from game screen)
if($lock) {
	release_lock();
}
$lock=false;
$session->destroy();

?>

<table cellspacing=0 cellpadding=0 border=0 valign=bottom>
<tr>
	<td width=100% align=left><h1>LOGOFF</h1></td>
</tr>
</table>

<table cellspacing=5 cellpadding=5 border=0>
<tr>
	<td width=600 height=100%>
		<p align="left">You have been logged off.</p>
		<p align="center"><img src="images/logoff.jpg" width="324" height="216"></p>
	</td>
</tr>
</table>