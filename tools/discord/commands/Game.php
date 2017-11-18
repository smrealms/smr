<?php

namespace App\Commands;

use Core\Command\Command;
use Core\Command\Parameters;

class Game extends Command
{
    protected $name = 'game';
    protected $description = 'Get name of game linked to this channel';

    public function index(Parameters $p)
    {
        catch_throws(function() {
            $link = new \GameLink($this->channel, $this->author);
            if (!$link->valid) return;

            $game = \SmrGame::getGame($link->player->getGameID(), true);
            $msg = "I am linked to game `".$game->getDisplayName()."` in this channel.";

            $this->channel->sendMessage($msg);
        });
    }
}
