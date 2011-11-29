ALTER TABLE chess_game_moves
ADD castling ENUM( 'King', 'Queen' ) NULL DEFAULT NULL;