<?php declare(strict_types=1);
$template->assign('PageTopic', 'Dump Cargo');

if ($ship->hasCargo()) {

	$goods = array();
	foreach ($ship->getCargo() as $goodID => $amount) {
		$container = Page::create('cargo_dump_processing.php');
		$container['good_id'] = $goodID;

		$goods[] = array(
			'image' => Globals::getGood($goodID)['ImageLink'],
			'name' => Globals::getGood($goodID)['Name'],
			'amount' => $amount,
			'dump_href' => $container->href(),
		);
	}

	$template->assign('Goods', $goods);
}
