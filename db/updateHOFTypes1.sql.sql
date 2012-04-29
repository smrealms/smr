UPDATE player_hof SET type = 'Dying:Players:Experience:Gained By Killer'
				WHERE type = 'Dying:Experience:Gained By Killer';
UPDATE player_hof SET type = 'Dying:Players:Money:Bounty Gained By Killer'
				WHERE type = 'Dying:Money:Bounty Gained By Killer';
--UPDATE player_hof SET amount = amount / 2
--				WHERE type = 'Dying:Money:Bounty Gained';