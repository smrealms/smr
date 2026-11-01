<?php declare(strict_types=1);

use Smr\ArrayValue;
use Smr\TypeHelpers\Type;

require_once('../bootstrap.php');

/**
 * @param array<mixed> $data
 */
function getImprintString(array $data, string $key): string {
	return ArrayValue::get($data, $key, Type::string());
}

/**
 * @template T = mixed
 * @param array<mixed> $data
 * @param ?Type<T> $valueType
 * @return array<array-key, T>
 */
function getImprintArray(array $data, string $key, ?Type $valueType = null): array {
	return ArrayValue::get($data, $key, Type::array(valueType: $valueType));
}

$json = file_get_contents('https://gitlab.fem-net.de/vorstand/fem-impressum/-/raw/main/imprint.json');
if ($json === false) {
	throw new Exception('Failed to fetch JSON imprint');
}
$data = json_decode($json, associative: true, flags: JSON_THROW_ON_ERROR);
if (!is_array($data)) {
	throw new Exception('Invalid JSON imprint');
}

$registerCourt = getImprintString($data, 'registerCourt');
$registrationNumber = getImprintString($data, 'registrationNumber');

$contact = getImprintArray($data, 'imprintContact');
$contactCountry = getImprintString(getImprintArray($contact, 'country'), 'en');
$contactName = getImprintString($contact, 'name');
$contactAddress = getImprintString($contact, 'address');
$contactPostcode = getImprintString($contact, 'postcode');
$contactCity = getImprintString($contact, 'city');
$contactEmail = getImprintString($contact, 'email');
$contactPhone = getImprintString($contact, 'phone');
$contactHomepage = getImprintString($contact, 'homepage');

$board = [];
foreach (getImprintArray($data, 'boardOfDirectors', Type::array()) as $member) {
	$role = getImprintArray($member, 'role');
	$board[] = getImprintString($role, 'en') . ': ' . getImprintString($member, 'name');
}

$lastUpdateTimestamp = strtotime(getImprintString($data, 'lastUpdate'));
if ($lastUpdateTimestamp === false) {
	throw new Exception('Invalid imprint update date');
}
$lastUpdated = date(DateTimeInterface::RFC7231, $lastUpdateTimestamp);

?>
<!DOCTYPE html>

<html>
	<head>
		<link rel="stylesheet" type="text/css" href="<?php echo asset_url(DEFAULT_CSS); ?>">
		<link rel="stylesheet" type="text/css" href="<?php echo asset_url(DEFAULT_CSS_COLOUR); ?>">
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
									<?php echo $contactName; ?><br />
									<?php echo $contactAddress; ?><br />
									<?php echo $contactPostcode; ?> <?php echo $contactCity; ?>, <?php echo $contactCountry; ?><br />
									Email: <a href="mailto:<?php echo $contactEmail; ?>"><?php echo $contactEmail; ?></a><br />
									Tel./Fax: <?php echo $contactPhone; ?><br />
									Internet: <a href="<?php echo $contactHomepage; ?>" target="_blank"><?php echo $contactHomepage; ?></a>
								</p>

								<h2>Board of Directors</h2>
								<p><?php
									echo implode('<br />', $board); ?><br />
									Register court: <?php echo $registerCourt; ?><br/>
									Registration number: <?php echo $registrationNumber; ?>
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
