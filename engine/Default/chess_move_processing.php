<?php
require_once(get_file_loc('ChessGame.class.inc'));
$chessGame = new ChessGame($var['ChessGameID']);
$template->assignByRef('ChessGame',$chessGame);
if(is_numeric($_REQUEST['x']) && is_numeric($_REQUEST['y']) && is_numeric($_REQUEST['toX']) && is_numeric($_REQUEST['toY']))
{
	$x = $_REQUEST['x'];
	$y = $_REQUEST['y'];
	$toX = $_REQUEST['toX'];
	$toY = $_REQUEST['toY'];
	if($chessGame->getEndDate()==null)
	{
		if($chessGame->isCurrentTurn($account->getAccountID()))
		{
			$board = $chessGame->getBoard();
			if($board[$y][$x] != null)
			{
				switch($chessGame->tryMove($x, $y, $toX, $toY, $account->getAccountID()))
				{
					case 0:
						//Success
						SmrPlayer::sendMessageFromCasino($player->getGameID(), $chessGame->getCurrentTurnPlayer(), 'It is now your turn in the chess game against [player=' . $player->getPlayerID() . ']');
					break;
					case 1:
						SmrPlayer::sendMessageFromCasino($player->getGameID(), $chessGame->getCurrentTurnPlayer(), 'You have just lost the chess game against [player=' . $player->getPlayerID() . ']');
					break;
					case 2:
						$template->assign('MoveMessage', 'There is no piece in that square.');
					break;
					case 3:
						$template->assign('MoveMessage', 'You cannot end your turn in check.');
					break;
				}
			}
			else
			{
//				this.logger.error('Player tried to move from an empty tile: username = ' + username + ', x = ' + xIn + ', y = ' + yIn + ', toX = ' + toXIn + ', toY = ' + toYIn);
			}
		}
		else
		{
//			this.logger.error('Player tried to move in an ended game');
			$template->assign('MoveMessage', 'This game is over.');
		}
	}
	else
	{
//		this.logger.error('Player tried to move when it was not their turn: x = ' + xIn + ', y = ' + yIn + ', toX = ' + toXIn + ', toY = ' + toYIn);
	}
}
else
{
//	this.logger.error('Player supplied an invalid number: x = ' + xIn + ', y = ' + yIn + ', toX = ' + toXIn + ', toY = ' + toYIn);
}
?>