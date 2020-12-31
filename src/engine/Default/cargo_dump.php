<?php declare(strict_types=1);
$template->assign('PageTopic', 'Dump Cargo');

if ($ship->hasCargo()) {

	$goods = array();
	foreach ($ship->getCargo() as $goodID => $amount) {
		$container = create_container('cargo_dump_processing.php');
		$container['good_id'] = $goodID;

		$goods[] = array(
			'image' => Globals::getGood($goodID)['ImageLink'],
			'name' => Globals::getGood($goodID)['Name'],
			'amount' => $amount,
			'dump_href' => SmrSession::getNewHREF($container),
		);
	}

	$template->assign('Goods', $goods);
}
