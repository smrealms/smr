<?

$container = array();

// is account validated?
if ($account->validated == "FALSE") {

	$container["url"] = "skeleton.php";
	$container["body"] = "validate.php";

} else
	$container["url"] = "announcements_check.php";

forward($container);

?>