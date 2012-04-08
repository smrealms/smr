<?php

if ($_POST["action"] == "Delete Entry")
	forward(create_container("skeleton.php", "album_delete_confirmation.php"));

// get location
if ($_POST["location"] != "N/A")
	$location = format_string($_POST["location"], TRUE);
else
	$location = "''";

// get email
if ($_POST["email"] != "N/A")
	$email = format_string($_POST["email"], TRUE);
else
	$email = "''";

// get website (and validate it)
if ($_POST["website"] != "http://") {

	// add http:// if missing
	if (!preg_match("=://=", $_POST["website"]))
		$website = "http://" . $_POST["website"];
	else
	  $website = $_POST["website"];

	// validate
	$status = floor(php_link_check($website, TRUE) / 100);

	if ($status != 2 && $status != 3)
		create_error("The website you entered is invalid!");

} else
	$website = "";

// get 'other' info
if ($_POST["other"] != "N/A")
	$other = format_string($_POST["other"], TRUE);
else
	$other = "''";

// get day
if ($_POST["day"] != "N/A")
	$day = $_POST["day"];
else
	$day = 0;

// get month
if ($_POST["month"] != "N/A")
	$month = $_POST["month"];
else
	$month = 0;

// get year
if ($_POST["year"] != "N/A")
	$year = $_POST["year"];
else
	$year = 0;

// check if these values are nummeric
if (!is_numeric($day))
	create_error("The day has to be a number!");
if (!is_numeric($month))
	create_error("The month has to be a number!");
if (!is_numeric($year))
	create_error("The year has to be a number!");

// check if we have an image
if ($_FILES['photo']['error'] == UPLOAD_ERR_OK) {

	// get dimensions
	$size = getimagesize($_FILES['photo']['tmp_name']);

	// check if we really have a jpg
	if ($size[2] < 1 || $size[2] > 3)
		create_error("Only gif, jpg or png-image allowed!");

	// check if width > 500
	if ($size[0] > 500)
		create_error("Image is wider than 500 pixels!");

	// check if height > 500
	if ($size[1] > 500)
		create_error("Image is higher than 500 pixels!");

	move_uploaded_file($_FILES['photo']['tmp_name'], UPLOAD.SmrSession::$old_account_id);

} else
	$no_picture = true;

// get current time
$curr_time = time();

// check if we had a album entry so far
$db->query("SELECT * FROM album WHERE account_id = ".SmrSession::$old_account_id);
if ($db->next_record()) {

	if ($no_picture == false)
		$comment = "<span style=\"color:lime;\">*** Picture changed</span>";

	// change album entry
	$db->query("UPDATE album
				SET location = $location,
					email = $email,
					website= '$website',
					day = $day,
					month = $month,
					year = $year,
					other = $other,
					last_changed = $curr_time,
					approved = 'TBC',
					disabled = 'FALSE'
				WHERE account_id = ".SmrSession::$old_account_id);

} else {

	// if he didn't upload a picture before
	// we kick him out here
	if ($no_picture)
		create_error("What is it worth if you don't upload an image?");

	$comment = "<span style=\"color:lime;\">*** Picture added</span>";

	// add album entry
	$db->query("INSERT INTO album (account_id, location, email, website, day, month, year, other, created, last_changed, approved) " .
			   "VALUES(".SmrSession::$old_account_id.", $location, $email, '$website', $day, $month, $year, $other, $curr_time, $curr_time, 'TBC')");

}

if ($comment) {

	// check if we have comments for this album already
	$db->lock("album_has_comments");

	$db->query("SELECT MAX(comment_id) FROM album_has_comments WHERE album_id = ".SmrSession::$old_account_id);
	if ($db->next_record())
		$comment_id = $db->f("MAX(comment_id)") + 1;
	else
		$comment_id = 1;

	$db->query("INSERT INTO album_has_comments
				(album_id, comment_id, time, post_id, msg)
				VALUES ($account->account_id, $comment_id, $curr_time, 0, '$comment')");
	$db->unlock();

}

$container = array();
$container["url"] = "skeleton.php";
if (SmrSession::$game_id > 0)
	if ($player->land_on_planet == "TRUE") $container["body"] = "planet_main.php"; else $container["body"] = "current_sector.php";
else
	$container["body"] = "game_play.php";

forward($container);

function php_link_check($url, $r = FALSE) {
	/*	Purpose: Check HTTP Links
	 *	Usage:	 $var = phpLinkCheck(absoluteURI)
	 *					 $var["Status-Code"] will return the HTTP status code
	 *					 (e.g. 200 or 404). In case of a 3xx code (redirection)
	 *					 $var["Location-Status-Code"] will contain the status
	 *					 code of the new loaction.
	 *					 See print_r($var) for the complete result
	 *
	 *	Author:	Johannes Froemter <j-f@gmx.net>
	 *	Date:		2001-04-14
	 *	Version: 0.1 (currently requires PHP4)
	 */
	$url = trim($url);
	if (!preg_match("=://=", $url)) $url = "http://$url";
	$url = parse_url($url);
	if (strtolower($url["scheme"]) != "http") return FALSE;

	if (!isset($url["port"])) $url["port"] = 80;
	if (!isset($url["path"])) $url["path"] = "/";

	if (!checkdnsrr($url["host"], "A"))
		return FALSE;

	$fp = fsockopen($url["host"], $url["port"], &$errno, &$errstr, 30);

	if (!$fp) return FALSE;
	else
	{
		$head = "";
		$httpRequest = "HEAD ". $url["path"] ." HTTP/1.1\r\n"
								."Host: ". $url["host"] ."\r\n"
								."Connection: close\r\n\r\n";
		fputs($fp, $httpRequest);
		while(!feof($fp)) $head .= fgets($fp, 1024);
		fclose($fp);

		preg_match("=^(HTTP/\d+\.\d+) (\d{3}) ([^\r\n]*)=", $head, $matches);
		$http["Status-Line"] = $matches[0];
		$http["HTTP-Version"] = $matches[1];
		$http["Status-Code"] = $matches[2];
		$http["Reason-Phrase"] = $matches[3];

		if ($r)
			return $http["Status-Code"];

		$rclass = array("Informational", "Success", "Redirection", "Client Error", "Server Error");
		$http["Response-Class"] = $rclass[$http["Status-Code"][0] - 1];

		preg_match_all("=^(.+): ([^\r\n]*)=m", $head, $matches, PREG_SET_ORDER);
		foreach($matches as $line) $http[$line[1]] = $line[2];

		return $http;
	}

}

?>