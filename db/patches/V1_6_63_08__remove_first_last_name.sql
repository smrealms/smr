-- Do not track account holder's name anymore
ALTER TABLE account DROP COLUMN first_name,
                    DROP COLUMN last_name;
