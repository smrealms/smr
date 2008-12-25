<?

// ********************************
// *
// * I n c l u d e s   h e r e
// *
// ********************************

include('config.inc');
require_once(LIB . 'global/smr_db.inc');
require_once(ENGINE . 'Old_School/smr.inc');
require_once(get_file_loc('SmrSession.class.inc'));
require_once(get_file_loc('SmrAccount.class.inc'));


// ********************************
// *
// * S e s s i o n
// *
// ********************************


if (SmrSession::$account_id > 0) {

	// creates a new user account object
	$account =& SmrAccount::getAccount(SmrSession::$account_id);

	// update last login column
	$account->update_last_login();

	$container = array();
	$container['url'] = 'validate_check.php';
	$sn = SmrSession::get_new_sn($container);
	SmrSession::update();

	header('Location: '.$URL.'/loader.php?sn='.$sn);
	exit;
}

// new db object
$db = new SMR_DB();

$loginNews = array();
$db->query('SELECT * FROM game_news ORDER BY time DESC LIMIT 3');
while ($db->next_record())
{
	$loginNews[] = array('Message' => $db->f('message'),'AdminName' => $db->f('admin_name'),'Time' => date('n/j/Y',$db->f('time')), 'Recent' => (time() - $db->f('time') < 24 * 3600));
}
$smarty->assign('LoginNews',$loginNews);


$db->query('SELECT count(*) AS num_on_cpl FROM player WHERE last_cpl_action > '.(time() - 3600));
$db->next_record();
$smarty->assign('NumberOnCPL',$db->f('num_on_cpl'));

$gameNews = array();
$db->query('SELECT * FROM news ORDER BY time DESC LIMIT 4');
while ($db->next_record())
{
	$gameNews[] = array('Date' => date('n/j/Y',$db->f('time')), 'Time' => date('g:i:s A',$db->f('time')), 'Message' => $db->f('news_message'));
}
$smarty->assign('GameNews',$gameNews);

include_once('story.php');

if(isset($_REQUEST['msg']))
	$smarty->assign('Message',htmlentities(trim($_REQUEST['msg'])));
$smarty->display('login.tpl');
/*
<!doctype html public '-//W3C//DTD HTML 4.0 Transitional//EN'>

<html>

<head>
	<link rel='stylesheet' type='text/css' href='default.css'>
	<meta http-equiv='Content-Type' content='text/html; charset=windows-1252'>
	<meta http-equiv='Content-Language' content='en-us'>
	<title>Space Merchant Realms</title>
	<style type='text/css'>
		td {
			text-align:center;
		}
	</style>
</head>

<body style='margin:10px;'>

<?php include('menu.html'); ?>

<form method='POST' action='login_processing.php'>
	<?php
	if (!empty($_GET['return_page']))
		$PHP_OUTPUT.=('<input type="hidden" name="return_page" value="' . $_GET['return_page'] . '">');
	?>
	<?php
	$launch = mktime(0,0,0,3,12,2008);
	$now = time();
	//if ($launch - $now > 0)
		//echo 'SMR 1.5 Launch in <span class='red'>' . format_time($launch - $now) . '</span>!<br />';
	?>
	<table border='0' cellpadding='0' cellspacing='1' width='85%'>
	<tr>
		<td align='center'><b style='color:red;font-size:125%;'><br>
			<?php
			if ($launch - $now > 0)
				echo 'SMR 1.5 Launch in ' . format_time($launch - $now) . '!<br />';
			?><br></b>
		</td>
	</tr>
	<tr>
		<td bgcolor='#0B8D35'>
			<table border='0' cellpadding='3' cellspacing='2' width='100%'>
			<tr bgcolor='#0B2121'>
				<td>
					<table border='0' cellpadding='3' cellspacing='2' width='100%'>
					<tr bgcolor='#0B8D35'>
						<td align='center'>
							<font face='Times New Roman' size='+1' color='#FFFFFF'>
								<p>USERNAME: <input type='text' name='login' size='16' maxlength='32'>&nbsp;&nbsp;&nbsp;PASSWORD: <input type='password' name='password' size='16' maxlength='32'>&nbsp;&nbsp;&nbsp;<input type='Submit' value='Login'></p>
								<p><strong><a href='resend_password.php'>Forgot your password?</a></strong></p>
								<p>Are you a new player? <strong><a href='login_create.php'>Create a new login here.</a></strong></p>
							</font>
						</td>
			        </tr>
					</table>
				</td>
	        </tr>
			</table>
		</td>
	</tr>
	</table>
</form>

<!--
<form action='http://www.mpogd.com/gotm/vote.asp' method='post'>
	<input type='hidden' name='gameid' value='1145'>
	<input type='hidden' name='stage' value='vote'>
	<input type='submit' name='Submit' value='Submit Vote'>
</form>
-->

<table border='0' width='85%' cellspacing='0' cellpadding='0'>
<tr>
	<td width='100%' colspan='2'>
		<table border='0' cellpadding='0' cellspacing='0' width='100%'>
		<tr>
			<td valign='top'>
				<div align='center'>
					<table border='0' width='640'>
					<tr>
						<td colspan='2'>
							<p align='justify'>
								<small>
									<font face='Verdana, Arial, Helvetica' color='#FFFFFF'>
										After unceasing wars between the various races,
										the cluster of galaxies in the known corner of the universe
										became uninhabitable. Severe solar storms swept
										through the trade lanes crushing any resources in the
										raveged waste land, destroying any ship that did not
										get out of the way. Whole worlds were laid waste between
										the frenzied fighting for the last remaining habitable
										areas and the harsh magnetic storms that stripped atmospheres
										in a matter of months.
									</font>
								</small>
							</p>
							<p align='justify'>
								<small>
									<font face='Verdana, Arial, Helvetica' color='#FFFFFF'>
										In a last desperate bid, what remained of the Federation
										council convinced the leaders of each race that it was
										time to flee the galaxy cluster and search for new homes.
										Each race went their own way, with old feelings hard to
										put down for the sake of survival.
									</font>
								</small>
							</p>
							<p align='justify'>
								<small>
									<font face='Verdana, Arial, Helvetica' color='#FFFFFF'>
										It is here where we find ourselves now.
										The Humans and Nijarins fled together and have each found
										their own corners in a new galactic cluster and have begun
										to spread throughout the galaxies once more.
										The current races were helpful in the beginning,
										setting aside their own squabbles to help settle the newcomers.
										But peace is a fragile thing and never lasts long,
										and the Galactic Council has troubles of its own...
									</font>
								</small>
							</p>
							<p align='justify'>
								<small>
									<font face='Verdana, Arial, Helvetica' color='#FFFFFF'><br>
										Space Merchant Realms is a game of skill, strategy, and
										roleplaying. Top rank isn't always determined by your
										skills at trading or fighting, but also by your ability
										to command, negotiate, and cooperate with your fellow
										alliancemates and other players. Those that can
										successfully do this can consider themselves some of
										the best players Space Merchant Realms has to offer.
									</font>
								</small>
							</p>
							<p align='center' style='font-size:75%;'>
								<small>
									<font face='Verdana, Arial, Helvetica' color='#FFFFFF'><br>
										<a href='imecho.html'>Imecho</a>
									</font>
								</small>
							</p>
						</td>
					</tr>
					</table>
				</div>
			</td>
		</tr>
		</table>
	</td>
</tr>
</table>

</html>

<?php
function format_time($seconds, $short=FALSE)
{
	$string = '';
	if ($seconds == 0)
	{
		$string = '0 seconds';
		if ($short) $string = '0s';
	}
	if ($seconds >= 60)
	{
		$minutes = floor($seconds/60);
		$seconds = $seconds % 60;
	}
	if ($minutes >= 60)
	{
		$hours = floor($minutes/60);
		$minutes = $minutes % 60;
	}
	if ($hours >= 24)
	{
		$days = floor($hours/24);
		$hours = $hours % 24;
	}
	if ($days >= 7)
	{
		$weeks = floor($days/7);
		$days = $days % 7;
	}
	if ($weeks > 0)
	{
		$string .= $weeks;
		if ($short) $string .= 'w';
		else
		{
			$string .= ' week';
			if ($weeks > 1) $string .= 's';
		}
	}
	if ($days > 0)
	{
		$before = $weeks;
		$after = $hours + $minutes + $seconds;
		if ($before > 0 && $after > 0) $string .= ', ';
		elseif ($before > 0 && $after == 0) $string .= ' and ';
		$string .= $days;
		if ($short) $string .= 'd';
		else
		{
			$string .= ' day';
			if ($days > 1) $string .= 's';
		}
	}
	if ($hours > 0)
	{
		$before = $weeks + $days;
		$after = $minutes + $seconds;
		if ($before > 0 && $after > 0) $string .= ', ';
		elseif ($before > 0 && $after == 0) $string .= ' and ';
		$string .= $hours;
		if ($short) $string .= 'h';
		else
		{
			$string .= ' hour';
			if ($hours > 1) $string .= 's';
		}
	}
	if ($minutes > 0)
	{
		$before = $weeks + $days + $hours;
		$after = $seconds;
		if ($before > 0 && $after > 0) $string .= ', ';
		elseif ($before > 0 && $after == 0) $string .= ' and ';
		$string .= $minutes;
		if ($short) $string .= 'm';
		else
		{
			$string .= ' minute';
			if ($minutes > 1) $string .= 's';
		}
	}
	if ($seconds > 0)
	{
		$before = $weeks + $days + $hours + $minutes;
		$after = 0;
		if ($before > 0 && $after > 0) $string .= ', ';
		elseif ($before > 0 && $after == 0) $string .= ' and ';
		$string .= $seconds;
		if ($short) $string .= 's';
		else
		{
			$string .= ' second';
			if ($seconds > 1) $string .= 's';
		}
	}
	return $string;
}*/
?>