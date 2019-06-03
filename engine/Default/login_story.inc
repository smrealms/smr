<?php

//////////////////////////////////////////////////
//
//	Script:		story.php
//	Purpose:	Stores info on storyline
//
//////////////////////////////////////////////////

$story = array(
	'It is a time of great interstellar turmoil....',
	'The decline of the Galactic Federation, which served for centuries as a glowing bastion of peace, freedom, and prosperity, has given way to a rising tide of corruption and organized crime. Trade lanes are unsafe even in the most well populated areas. Pirates disrupt the free flow of cash and goods to local economies. Organized gangs have laid claim to entire planets and control the most lucrative ports by threat of violence. Black market goods are sold in the open without fear.',
	'Sensing that the Federation has lost its grip on power, the eight known races have split off and formed independent governments. The presidents and their councils guide the flow of weapons and trade in a bid to retain economic control and keep their people safe. Despite these efforts, violence and desperation continues to escalate.',
	'This is the universe you find yourself in.',
	'Space Merchant Realms is a game of speed, skill, and strategy. To make it to the top ranks, it takes a combination of leadership, persistence, courage, and cooperation. Will you quest for riches as a tradeship captain? Or seek fame and glory as an alliance fleet commander? Is the life of a pirate what you are after? Where will your destiny take you?'
);
$template->assign('Story', $story);

//$story = array();
//$story[0] = 'After unceasing wars between the various races, the cluster of galaxies in the known corner of the universe became uninhabitable. Severe solar storms swept through the trade lanes crushing any resources in the ravaged waste land and destroying any ship that did not get out of the way. Whole worlds were laid to waste between the frenzied fighting for the last remaining habitable areas and the harsh magnetic storms that stripped atmospheres in a matter of months.';
//$story[1] = 'In a last desperate bid, what remained of the Galactic Council convinced several leaders of each race that it was time to flee the galaxy cluster and search for new homes. Seven races left with the council in search of a new home, while the rest remained to try and rebuild. After years of searching several members of each race, now fed up with the councils lack of leadership, left the relative safety of the council\'s fleet. Two years passed and still no home had been found. The members who left looking for their own place in the universe have still not been heard from.<br />';
//$story[2] = 'It is here where we find ourselves now. The traveling fleet of the council has decided to setup a temporary headquarters and give the races rest. The leaders of the races agreed with the council and setup their own bases and former businessmen of the races setup trading ports and shops of every kind. This caused great dissent among many members of the council, who then left to form a new government calling themselves "The Underground". With this split in the council, the remaining members of the council decided change was needed. They started by changing their name to the "Federal Government of the Seven". Within hours of the bases being setup, the new government began to receive several distress messages. Concerned that these messages might be part of their fleet they immediately sent out scout vessels with heavily armed escorts to all five distress calls.<br />';
//$story[3] = 'The first convoy reported back with excitement. They had found the Nijarin fleet who had left the old galaxy shortly after the rest of the races and had been trying to contact them for sometime. The Nijarins gladly joined the federal fleet and thus the Federal Government of the Seven became the Federal Government of the Eight.<br />';
//$story[4] = 'The report from the second convoy was just as exciting. They had found inhabitable planets, some of which had thriving communities.<br />';
//$story[5] = 'The third convoy, the strongest that the council sent out reported back with grim news. This is the only message received by the council: "This is convo... avo...der atta... they call them... Ere...s... they... too stron... don... ckup... repeat, do not... backup." <br />';
//$story[6] = 'The fourth and fifth convoys have yet to report back in.<br /><br />';
//$story[7] = 'Space Merchant Realms is a game of skill and strategy. Top rank isn\'t always determined by your skills at trading or fighting, but also by your ability to command, negotiate, and cooperate with your fellow alliancemates and other players. Those that can successfully do this can consider themselves some of the best players Space Merchant Realms has to offer. ';
//
////which parts of the story are available
//$story_available = array(0,1,2,3,4,5,6,7);
//
//if (count($story_available) > 0) {
//	$templateStory = array();
//	foreach ($story_available as $story_id) {
//		$templateStory[] = $story[$story_id];
//	}
//	$template->assign('Story', $templateStory);
//}
