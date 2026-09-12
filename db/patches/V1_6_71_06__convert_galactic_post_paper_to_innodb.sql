-- MyISAM assigns paper_id separately for each game. Give existing papers
-- global IDs and remap article associations before changing the primary key.
ALTER TABLE galactic_post_paper
	MODIFY paper_id INT UNSIGNED NOT NULL;

ALTER TABLE galactic_post_paper
	ADD new_paper_id INT UNSIGNED NOT NULL AUTO_INCREMENT FIRST,
	ADD UNIQUE KEY (new_paper_id);

UPDATE galactic_post_paper_content
JOIN galactic_post_paper USING (game_id, paper_id)
SET galactic_post_paper_content.paper_id = galactic_post_paper.new_paper_id;

ALTER TABLE galactic_post_paper
	DROP PRIMARY KEY,
	DROP paper_id,
	RENAME COLUMN new_paper_id TO paper_id,
	ADD PRIMARY KEY (paper_id);

ALTER TABLE galactic_post_paper
	DROP KEY new_paper_id,
	ADD KEY (game_id),
	ENGINE = InnoDB;
