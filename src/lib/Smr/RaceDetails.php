<?php declare(strict_types=1);

namespace Smr;

/**
 * Detailed race information for the Game Join page.
 */
class RaceDetails {

	private const SHORT_DESCRIPTION = [
		RACE_ALSKANT => 'Diverse assortment of hardware, but no dedicated warship. Trade bonuses with all races.',
		RACE_CREONTI => 'Bulky ships reinforced with heavy armour plating and lots of firepower.',
		RACE_HUMAN => 'Jump drive technology which enables fast inter-galactic movement.',
		RACE_IKTHORNE => 'Most overall defense, relying heavily on swarms of combat drones.',
		RACE_SALVENE => 'Illusion Generator technology which allows ships to mask their full strength.',
		RACE_THEVIAN => 'Fastest racial ships in the universe.',
		RACE_WQHUMAN => 'Cloaking Device technology which allows ships to hide from lower level traders.',
		RACE_NIJARIN => 'Strong weapons and Drone Communication Scrambler technology offsets lower defenses.',
	];

	private const LONG_DESCRIPTION = [
		RACE_ALSKANT => 'This race of tall, thin humanoids has just recently (in the last 100 years) discovered interstellar travel. However, their charisma and enterprising nature has allowed them to trade for much of the advanced technology the other races had already developed. Alskants are generally peaceful, and have designed their ships for commerce rather than war. Since they pose no military threat, they tend to have relatively good relationships with the other races. They continue to seek the knowledge of the cosmos, and to explore to the edges of space.',
		RACE_CREONTI => 'As the manufacturers of the most colossal warships known to history, the Creonti are perceived as formidable opponents in war. The fact that they stand over 11 feet tall does not soften their brutish reputation. While a focus on war-waging has long served the Creonti in their pursuit of resources, it has also severely stunted their culture in almost every other aspect. Compared to the other races, the Creonti lack advanced technology and only have a rudimentary economy. Painfully aware of this weakness, the Creonti are now attempting to leverage peace for assistance with trade and economic development.',
		RACE_HUMAN => 'After discovering Jump Drive technology, the Human race underwent a space exploration renaissance marked by many noble and altruistic deeds, including an attempt to unify all of the races through the creation of The Federation. Over time, the Human military grew in power and co-opted the Jump Drive for the purposes of conquest and colonization, weakening the Federation and dissolving any hope for true unification. Today, the Humans continue down this hegemonic path, fighting in wars over resources, and using the crumbling Federation to legitimize their political interests.',
		RACE_IKTHORNE => 'The Ik\'Thorne are an ancient race known for their extremely long life spans and expertise with autonomous drone systems. They were the first space-faring civilization, and amassed incalculable wealth due to their unfettered access to the cosmos. As more races ascended to the interstellar stage, the Ik\'Thorne became targets to be plundered for their resources and knowledge. Weary from endless conflict, the Ik\'Thorne grew introverted and isolationist, and they withdrew from the affairs of the other races. But their long lives give them long memories, which have not forgotten the transgressions of the past.',
		RACE_SALVENE => 'The Salvene are a species of hive-like quadrupeds that have long sought dominion over the other races. This is their prime objective, which they have been maneuvering to advance through alliances of convenience and opportunistic declarations of war. They are quite ruthless, and have been known to violate peace treaties when it is to their advantage. Even in battle, the Salvene employ Illusion Generator technology to deceive their enemies, often tricking them into attacking what appears to be a weak and vulnerable ship. These behaviors have led to a growing distrust of the Salvene, putting them in a position where they can only rely on each other for help.',
		RACE_THEVIAN => 'The Thevian are a race of cybernetic creatures that are encased in a biomechanoid shell. This shell gives them a humanoid appearance, but their true form is shrouded in mystery and known to few outside the Thevian territories. It also gives them supernatural strength and reflexes, making them some of the best pilots around. Thevians are uniquely individualistic, with their desire for personal renown often driving them to extreme behavior. Some become vigilantes, relentlessly hunting down evil throughout the galaxy, even if no bounty is set. Others take the opposite path, becoming the most notorious criminals in the galaxy for their acts of destruction and cruelty.',
		RACE_WQHUMAN => 'The WQ Humans are a rogue faction of the Human race who withdrew from the Human territories in opposition to the creation of the Federation. They settled in the distant Western Quadrant, where they developed their new society and began extensive trading with the other races. Ever fearful of retribution for their rebellion, the WQ Humans outfitted their ships with Cloaking Device technology to remain inconspicuous. The WQ Humans may not be the most powerful race, but they are by far the most resourceful.',
		RACE_NIJARIN => 'The Nijarin are a race of six-limbed reptilian creatures. They have existed just as long if not longer than the other races, but have only recently come out of hiding. As a naturally aggressive race, the Nijarin have already become fearsome contenders in the war for resources. Their focus is on offensive power, which has driven the creation of ships that are overloaded with destructive weaponry. To support such a heavy offensive payload, these ships have suffered defensively due to a reduction in shields and armour. The Nijarin use a technology called the Drone Communications Scrambler to bolster their defenses against enemy drones. The Nijarin fleet cannot be held back from taking dominion over this universe of war.',
	];

	public static function getShortDescription(int $raceID) : string {
		return self::SHORT_DESCRIPTION[$raceID];
	}

	public static function getLongDescription(int $raceID) : string {
		return self::LONG_DESCRIPTION[$raceID];
	}

}
