<?php

namespace App\Commands;

use Core\Command\Command;
use Core\Command\Parameters;

class Turns extends Command
{
    protected $name = 'turns';
    protected $description = 'Get current turns';

    public function index(Parameters $p)
    {
        catch_throws(function() {
            $link = new \GameLink($this->channel, $this->author);
            if (!$link->valid) return;

            $player = $link->player;
            $this->channel->sendMessage($player->getPlayerName() . " has " . $player->getTurns() . " turns");
        });
    }
}
