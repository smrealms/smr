-- Add `admin` enum to the `news.type` column.
ALTER TABLE news MODIFY COLUMN type enum('breaking','regular','lotto','admin') NOT NULL DEFAULT 'regular';
