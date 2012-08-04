UPDATE account_has_credits
SET credits_left = credits_left * 10,
	reward_credits = reward_credits * 10;
UPDATE bounty
SET smr_credits = smr_credits * 10;