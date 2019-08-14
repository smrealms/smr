<?php

const NPC_GAME_ID = 1;
const NPC_LOG_TO_DATABASE = true;  // insert debug messages into db

const NPC_LOW_TURNS = 75;
const NPC_LOW_NEWBIE_TURNS = 10;
const MINUMUM_RESERVE_CREDITS = 100000;
const MIN_NEWBIE_TURNS_TO_BUY_CARGO = 50;
const MIN_SLEEP_TIME = 800000;
const MAX_SLEEP_TIME = 1100000;

const UCI_CHESS_ENGINE = '/path/to/UCI_CHESS_ENGINE'; // Stockfish works: http://www.stockfishchess.com
const UCI_TIME_PER_MOVE_MS = 2000;
const UCI_SLEEP_BETWEEN_CYCLES_US = 2000000;
const UCI_HASH_SIZE_MB = 512;
