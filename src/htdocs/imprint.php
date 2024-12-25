<?php declare(strict_types=1);

require_once('../bootstrap.php');

$json = file_get_contents('https://gitlab.fem-net.de/vorstand/fem-impressum/-/raw/main/imprint.json');
if ($json === false) {
	throw new Exception('Failed to fetch JSON imprint');
}
$data = json_decode($json, flags: JSON_THROW_ON_ERROR);
$contact = $data->imprintContact;
$board = array_map(
	fn(stdClass $member): string => $member->role->en . ': ' . $member->name,
	$data->boardOfDirectors,
);
$lastUpdated = date(DateTimeInterface::RFC7231, strtotime($data->lastUpdate));

?>
<!DOCTYPE html>

<html>
	<head>
		<link rel="stylesheet" type="text/css" href="<?php echo DEFAULT_CSS; ?>">
		<link rel="stylesheet" type="text/css" href="<?php echo DEFAULT_CSS_COLOUR; ?>">
		<title>Imprint</title>
		<meta http-equiv='pragma' content='no-cache'>
	</head>

	<body>

		<table cellspacing='0' cellpadding='0' border='0' width='100%' height='100%'>
			<tr>
				<td></td>
				<td colspan='3' height='1' bgcolor='#0B8D35'></td>
				<td></td>
			</tr>
			<tr>
				<td valign='top' width='135'>&nbsp;</td>
				<td width='1' bgcolor='#0B8D35'></td>
				<td valign='top' width='600' bgcolor='#06240E'>
					<table width='100%' height='100%' border='0' cellspacing='5' cellpadding='5'>
						<tr>
							<td style='vertical-align:top;'>

								<h1>Imprint</h1>

								<p><br /></p>

								<h2>Contact</h2>
								<p>
									<?php echo $contact->name; ?><br />
									<?php echo $contact->address; ?><br />
									<?php echo $contact->postcode; ?> <?php echo $contact->city; ?>, <?php echo $contact->country->en; ?><br />
									Email: <a href="mailto:<?php echo $contact->email; ?>"><?php echo $contact->email; ?></a><br />
									Tel./Fax: <?php echo $contact->phone; ?><br />
									Internet: <a href="<?php echo $contact->homepage; ?>" target="_blank"><?php echo $contact->homepage; ?></a>
								</p>

								<h2>Board of Directors</h2>
								<p><?php
									echo implode('<br />', $board); ?><br />
									Register court: <?php echo $data->registerCourt; ?><br/>
									Registration number: <?php echo $data->registrationNumber; ?>
								</p>

								<h2>Responsible for Contents</h2>
								<p>
									through &sect; 55 Abs. 2 RStV: Michael Kunze
								</p>

								Last updated: <?php echo $lastUpdated; ?>
							</td>
						</tr>
					</table>
				</td>
				<td width='1' bgcolor='#0B8D35'></td>
				<td valign='top' width='135'>&nbsp;</td>
			</tr>
			<tr>
				<td></td>
				<td colspan='3' height='1' bgcolor='#0b8d35'></td>
				<td></td>
			</tr>
		</table>

	</body>
</html>
