ALTER TABLE message
CHANGE reciever_delete
	receiver_delete ENUM('TRUE', 'FALSE') NOT NULL DEFAULT 'FALSE';