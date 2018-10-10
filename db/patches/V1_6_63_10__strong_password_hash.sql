-- Use recommended type to future proof password hashes
ALTER TABLE account MODIFY password varchar(255) NOT NULL;
