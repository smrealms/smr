-- Make `galactic_post_paper.paper_id` an auto-increment column
ALTER TABLE `galactic_post_paper`
	MODIFY `paper_id` int unsigned NOT NULL AUTO_INCREMENT;
