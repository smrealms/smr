-- The message sidebar reads unread counts from message directly. This index
-- covers its player, visibility, unread-state, and message-type lookup.
ALTER TABLE message
	ADD KEY unread_by_player (player_id, receiver_delete, msg_read, message_type_id);

DROP TABLE player_has_unread_messages;
