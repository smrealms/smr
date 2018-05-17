-- Add `newbie_status` column to the `player` table.
ALTER TABLE player ADD COLUMN newbie_status enum('TRUE','FALSE') NOT NULL;

-- Update this column for all existing `player` entries.
UPDATE player JOIN account USING (account_id) SET newbie_status=
CASE
    WHEN (veteran = 'TRUE' OR max_rank_achieved >= 3) THEN 'FALSE'
    ELSE 'TRUE'
END;
