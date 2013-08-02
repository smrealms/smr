-- This is to remove message_type_id from this index, it's getting its own index.
ALTER TABLE message
DROP INDEX game_id,
ADD INDEX game_id (
	game_id
,	account_id
,	receiver_delete
);

-- This has been moved out of the other index as we at times want to filter just by the message type, for instance in deleting old scout messages.
ALTER TABLE message ADD INDEX (
	message_type_id
);

-- This index is used on logging in, to expire out of date messages.
ALTER TABLE message ADD INDEX (
	expire_time
);
