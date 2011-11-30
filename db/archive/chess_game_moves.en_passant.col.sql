ALTER TABLE chess_game_moves
ADD en_passant ENUM( 'TRUE', 'FALSE' ) DEFAULT 'FALSE';