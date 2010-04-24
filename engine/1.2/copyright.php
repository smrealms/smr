<?php

print("<table border=\"0\" width=\"100%\">");
print("<tr>");
print("<td valign=\"middle\">");
$container=array();
$container["url"] = "vote_link.php";

print("<br><center><span style=\"font-size:75%;\">Get <b><u>FREE TURNS</u></b> for voting if you see the star.</span></center>");

$db->query("SELECT timeout FROM vote_links WHERE account_id=" . SmrSession::$old_account_id . " AND link_id=1 LIMIT 1");
if(isset(SmrSession::$game_id) && SmrSession::$game_id>0 && (!$db->next_record() || $db->f("timeout") < (time() - 86400)) && rand(0,100) < 80) {
$container["link_id"] = 1;
	print"<a href='javascript:window.open(\"http://www.mpogd.com/gotm/vote.asp\");document.location=\"".URL."/loader2.php?sn=" . SmrSession::get_new_sn($container) . "\"'><img border=\"0\" src=\"images/game_sites/mpogd_vote.png\" width=\"98\" height=\"41\"></a>";
}
else {
$container["link_id"] = 0;
	print("<a href=\"http://www.mpogd.com/gotm/vote.asp\" target=\"_game_site\"><img border=\"0\" src=\"images/game_sites/mpogd.png\" width=\"98\" height=\"41\"></a>&nbsp;&nbsp;");
}


$db->query("SELECT timeout FROM vote_links WHERE account_id=" . SmrSession::$old_account_id . " AND link_id=2 LIMIT 1");
if(isset(SmrSession::$game_id) && SmrSession::$game_id>0 && (!$db->next_record() || $db->f("timeout") < (time() - 86400)) && rand(0,100) < 80) {
$container["link_id"] = 2;
	print"<a href='javascript:window.open(\"http://www.topwebgames.com/in.asp?id=136\");document.location=\"".URL."/loader2.php?sn=" . SmrSession::get_new_sn($container) . "\"'><img border=\"0\" src=\"images/game_sites/twg_vote.png\" width=\"98\" height=\"41\"></a>";
}
else {
$container["link_id"] = 0;
	print("<a href=\"http://www.topwebgames.com/in.asp?id=136\" target=\"_game_site\"><img border=\"0\" src=\"images/game_sites/twg.png\" width=\"98\" height=\"41\"></a>&nbsp;&nbsp;");
}


$db->query("SELECT timeout FROM vote_links WHERE account_id=" . SmrSession::$old_account_id . " AND link_id=3 LIMIT 1");
if(isset(SmrSession::$game_id) && SmrSession::$game_id>0 && (!$db->next_record() || $db->f("timeout") < (time() - 86400)) && rand(0,100) < 80) {
$container["link_id"] = 3;
	print"<a href='javascript:window.open(\"http://www.omgn.com/topgames/vote.php?Game_ID=30\");document.location=\"".URL."/loader2.php?sn=" . SmrSession::get_new_sn($container) . "\"'><img border=\"0\" src=\"images/game_sites/omgn_vote.jpg\" width=\"98\" height=\"41\"></a>";
}
else {
$container["link_id"] = 0;
	print("<a href=\"http://www.omgn.com/topgames/vote.php?Game_ID=30\" target=\"_game_site\"><img border=\"0\" src=\"images/game_sites/omgn.jpg\" width=\"98\" height=\"41\"></a>&nbsp;&nbsp;");
}



print("</td>");

$db = new SmrMySqlDatabase();
$db->query("SELECT *
			FROM version
			ORDER BY went_live DESC
			LIMIT 1
		   ");

if ($db->next_record()) {

	$version_id = $db->f("version_id");

	$container = array('url' => 'skeleton.php',
					   'body' => 'changelog_view.php',
					   'version_id' => $version_id
					  );
	$version = create_link($container, 'v' . $db->f('major_version') . '.' . $db->f('minor_version') . '.' . $db->f('patch_level'));

}

$this_year = date("Y");
$time_elapsed = getmicrotime() - $time_start;
print("<td align=\"right\" width=\"100%\" style=\"vertical-align:bottom\"><span style=\"font-size:75%;\">Space Merchant Realms<br>$version&nbsp;&nbsp;&nbsp;&copy; 2001-$this_year<br>hosted by <a href=\"http://www.fem.tu-ilmenau.de/fem_neu/scripts/index.php?fuseaction=page.content&id=1&menue_name=englisch\" target=\"fem\">FeM</a><br />Script runtime: " . number_format($time_elapsed, 3) . " sec</span></td>");
print("</tr>");
print("</table>");

?>
