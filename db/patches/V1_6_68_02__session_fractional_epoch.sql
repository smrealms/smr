-- Convert active_session.last_accessed from int to double.
-- Also drop default value, since it should not have one.
ALTER TABLE `active_session` MODIFY `last_accessed` DOUBLE NOT NULL;
