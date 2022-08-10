<?php declare(strict_types=1);

		$template = Smr\Template::getInstance();
		$var = Smr\Session::getInstance()->getCurrentVar();

		$template->assign('PageTopic', 'Combat Simulator');

		$template->assign('EditDummysLink', Page::create('admin/edit_dummys.php')->href());
		$template->assign('DummyNames', DummyShip::getDummyNames());

		$duplicates = false;

		$attackers = $var['attackers'] ?? [];
		for ($i = count($attackers) + 1; $i <= MAXIMUM_PVP_FLEET_SIZE; ++$i) {
			$attackers[$i] = null;
		}
		$template->assign('Attackers', $attackers);

		$defenders = $var['defenders'] ?? [];
		for ($i = count($defenders) + 1; $i <= MAXIMUM_PVP_FLEET_SIZE; ++$i) {
			$defenders[$i] = null;
		}
		$template->assign('Defenders', $defenders);

		$template->assign('Duplicates', $duplicates);

		$template->assign('CombatSimHREF', Page::create('admin/combat_simulator_processing.php')->href());

		if (isset($var['results'])) {
			$template->assign('TraderCombatResults', $var['results']);
		}
