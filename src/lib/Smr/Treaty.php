<?php declare(strict_types=1);

namespace Smr;

class Treaty {

	/**
	 * For each treaty type (as given by the columns in the alliance_treaties
	 * database table), provides an array with a display title and description.
	 */
	public const array TYPES = [
		'trader_assist' => [
			'Assist - Trader Attacks',
			'Assist your ally in attacking traders.',
		],
		'raid_assist' => [
			'Assist - Planet &amp; Port Attacks',
			'Assist your ally in attacking planets and ports.',
		],
		'trader_defend' => [
			'Defend - Trader Attacks',
			'Defend your ally when they are attacked.',
		],
		'trader_nap' => [
			'Non Aggression - Traders',
			'Cease Fire against Traders.',
		],
		'planet_nap' => [
			'Non Aggression - Planets',
			'Cease Fire against Planets.',
		],
		'forces_nap' => [
			'Non Aggression - Forces',
			'Cease Fire against Forces. Also allows refreshing of allied forces.',
		],
		'aa_access' => [
			'Alliance Account Access',
			'Restrictions can be set in the roles section.',
		],
		'mb_read' => [
			'Message Board Read Rights',
			'Allow your ally to read your message board.',
		],
		'mb_write' => [
			'Message Board Write Rights',
			'Allow your ally to post on your message board.',
		],
		'mod_read' => [
			'Message of the Day Read Rights',
			'Allow your ally to read your message of the day.',
		],
		'planet_land' => [
			'Planet Landing Rights',
			'Allow your ally to land on your planets.',
		],
	];

}
