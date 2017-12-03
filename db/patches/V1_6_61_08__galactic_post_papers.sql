-- Merge galactic_post_online and galactic_post_papers
ALTER TABLE galactic_post_paper ADD COLUMN online_since int(10) unsigned DEFAULT NULL;
UPDATE galactic_post_paper t1 INNER JOIN galactic_post_online t2 ON t1.game_id = t2.game_id AND t1.paper_id = t2.paper_id SET t1.online_since = t2.online_since;
DROP TABLE galactic_post_online;
