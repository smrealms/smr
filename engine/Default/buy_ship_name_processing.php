<?php
$action = $_REQUEST['action'];
$name = isset($var['ShipName']) ? $var['ShipName'] : $_REQUEST['ship_name'];

$actionHtmlShipName = 'Include HTML (' . CREDITS_PER_HTML_SHIP_NAME . ' SMR Credits)';
$actionTextShipName = 'Get It Painted! (' . CREDITS_PER_TEXT_SHIP_NAME . ' SMR Credit)';
$actionShipLogo = 'Paint a logo (' . CREDITS_PER_SHIP_LOGO . ' SMR Credits)';

if (isset($var['ShipName']) || $action == $actionHtmlShipName) {
	$cred_cost = CREDITS_PER_HTML_SHIP_NAME;
}
else if ($action == $actionShipLogo) {
	$cred_cost = CREDITS_PER_SHIP_LOGO;
}
else if ($action == $actionTextShipName) {
	$cred_cost = CREDITS_PER_TEXT_SHIP_NAME;
}
else {
	throw new Exception('Did not match an expected ship name type.');
}

if ($account->getTotalSmrCredits() < $cred_cost) {
	create_error('You don\'t have enough SMR Credits. Donate to SMR to gain SMR Credits!');
}

if (!isset($var['ShipName'])) {
	if ($action == $actionShipLogo) {
		// check if we have an image
		if ($_FILES['photo']['error'] == UPLOAD_ERR_OK) {
			// get dimensions
			$size = getimagesize($_FILES['photo']['tmp_name']);
			if (!isset($size)) {
				create_error('Uploaded file must be an image!');
			}

			// check if we really have a jpg
			$allowed_types = [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG];
			if (!in_array($size[2], $allowed_types)) {
				create_error('Only gif, jpg or png-image allowed! s = ' . $size[2]);
			}

			// check if width > MAX_IMAGE_WIDTH
			if ($size[0] > MAX_IMAGE_WIDTH) {
				create_error('Image is wider than ' . MAX_IMAGE_WIDTH . ' pixels!');
			}

			// check if height > MAX_IMAGE_HEIGHT
			if ($size[1] > MAX_IMAGE_HEIGHT) {
				create_error('Image is taller than ' . MAX_IMAGE_HEIGHT . ' pixels!');
			}
			if (filesize($_FILES['photo']['tmp_name']) > MAX_IMAGE_SIZE * 1024) {
				create_error('Image is bigger than ' . MAX_IMAGE_SIZE . 'k.');
			}

			$filename = $player->getAccountID() . 'logo' . $player->getGameID();
			$name = '<img style="padding:3px;" src="upload/' . $filename . '">';
			if (!move_uploaded_file($_FILES['photo']['tmp_name'], UPLOAD . $filename)) {
				create_error('Failed to upload file');
			}
			$db->query('REPLACE INTO ship_has_name (game_id, account_id, ship_name)
						VALUES (' . $db->escapeNumber($player->getGameID()) . ', ' . $db->escapeNumber($player->getAccountID()) . ', ' . $db->escapeString($name) . ')');
			$account->decreaseTotalSmrCredits($cred_cost);
			$container = create_container('skeleton.php', 'current_sector.php');
			$container['msg'] = 'Your logo was successfully painted!';
			forward($container);
		}
		else {
			create_error('Error while uploading');
		}
	}

	if ($name == 'Enter Name Here') {
		create_error('Please enter a ship name!');
	}

	// disallow certain ascii chars
	for ($i = 0; $i < strlen($name); $i++) {
		if (ord($name[$i]) < 32 || ord($name[$i]) > 127 || in_array(ord($name[$i]), array(37, 39, 59, 92, 63, 42))) {
			create_error('The ship name contains invalid characters! ' . chr(ord($name[$i])));
		}
	}

	if ($action == $actionHtmlShipName) {
		$max_len = 128;
		//check for some bad html
		if (preg_match('/(\<span[^\>]*id\s*=)|(class\s*=\s*"[^"]*ajax)/i', $name) > 0) {
			create_error('You have used html that is not allowed.');
		}
		$bad = array('<form', '<applet', '<a ', '<bgsound', '<body', '<meta', '<dd', '<dir', '<dl', '<!doctype', '<dt', '<embed', '<frame', '<head', '<hr', '<iframe', '<ilayer', '<img', '<input', '<isindex', '<layer', '<li', '<link', '<map', '<menu', '<nobr', '<no', '<object', '<ol', '<opt', '<p', '<script', '<select', '<sound', '<td', '<text', '<t', '<ul', '<h', '<br', '</marquee><marquee', 'size', 'width', 'height', '<div', 'width=', '</marquee>%<marquee', '</marquee>?');
		foreach ($bad as $check) {
			if (stristr($name, $check)) {
				$check .= '*>';
				if ($check != '<h*>' && $check != '</marquee>?*>') {
					create_error(htmlentities($check, ENT_NOQUOTES, 'utf-8') . ' tag is not allowed in ship names.<br /><small>If you believe the name is appropriate please contact an admin.</small>');
				}
				else if ($check == '</marquee>?*>') {
					create_error('Sorry no text is allowed to follow a ' . htmlentities('</marquee>', ENT_NOQUOTES, 'utf-8') . ' tag.');
				}
				else {
					create_error('Either you used the ' . htmlentities($check, ENT_NOQUOTES, 'utf-8') . ' tag which is not allowed or the ' . htmlentities('<html>', ENT_NOQUOTES, 'utf-8') . ' tag which is not needed.');
				}
			}
		}
		list ($first, $second) = explode('</marquee>', $name);
		if ($second != '') {
			create_error('Sorry no text is allowed to follow a ' . htmlentities('</marquee>', ENT_NOQUOTES, 'utf-8') . ' tag.');
		}

		list ($first, $second) = explode('<marquee>', $name);
		if ($first != '' && $second != '') {
			create_error('Sorry no text is allowed to come before a ' . htmlentities('<marquee>', ENT_NOQUOTES, 'utf-8') . ' tag.');
		}

		//lets try to see if they closed all tags
		$first = explode('<', $name);
		foreach ($first as $second) {
			if ($second == '') {
				continue;
			}
			if (strpos($second, '/') !== false) {
				$open -= 1;
				$close += 1;
				if ($open < 0) {
					$ha = TRUE;
				}
			}
			else {
				$real_open += 1;
				$open += 1;
			}
		}
		if ($open > 0) {
			create_error('You must close all HTML tags.  (i.e a &lt;font color="red"&gt tag must have a &lt;/font&gt; tag somewhere after it).<br /><small>If you think you received this message in error please contact an admin.');
		}
		if ($close > $real_open || $ha || $open < 0) {
			create_error('You can not close tags that do not exist!<br /><small>This could be an attempt at hacking if this action is seen again it will be logged</small>');
		}
	}
	else {
		$max_len = 48;
		$name = htmlentities($name, ENT_NOQUOTES, 'utf-8');
	}
	if (strlen($name) > $max_len) {
		create_error('That won\'t fit on your ship!');
	}

	if ($action == $actionHtmlShipName) {
		$container = create_container('skeleton.php', 'buy_ship_name.php');
		$container['Preview'] = $name;
		forward($container);
	}
}

$db->query('REPLACE INTO ship_has_name (game_id, account_id, ship_name)
			VALUES (' . $db->escapeNumber($player->getGameID()) . ', ' . $db->escapeNumber($player->getAccountID()) . ', ' . $db->escapeString($name) . ')');
$account->decreaseTotalSmrCredits($cred_cost);

$message = 'Thanks for your purchase! Your ship is ready!';
if (isset($var['ShipName'])) {
	$message .= '<br />If your ship is found to use HTML inappropriately you may be banned.  If your ship does contain inappropriate HTML talk to an admin ASAP.';
}
$container = create_container('skeleton.php', 'current_sector.php');
$container['msg'] = $message;
forward($container);
