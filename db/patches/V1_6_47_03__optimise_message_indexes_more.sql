-- This is to speed up logging in.
ALTER TABLE message DROP INDEX game_id,
ADD INDEX (
	account_id
,	receiver_delete
)

ALTER TABLE message ADD INDEX (
	game_id
);
