-- Remove unused table (we use `planet_has_cargo` instead)
DROP TABLE planet_has_goods;

-- Remove unused fields from the `account` table
ALTER TABLE account DROP COLUMN address,
                    DROP COLUMN city,
                    DROP COLUMN postal_code,
                    DROP COLUMN country_code,
                    DROP COLUMN icq;
