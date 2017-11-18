<?php

namespace App\Commands;

use Core\Command\Command;
use Core\Command\Parameters;

require_once(TOOLS . 'chat_helpers/channel_msg_money.php');


class Money extends Command
{
    protected $name = 'money';
    protected $description = 'Get alliance financial status';

    public function index(Parameters $p)
    {
        catch_throws(function() {
            $link = new \GameLink($this->channel, $this->author);
            if (!$link->valid) return;

            $result = shared_channel_msg_money($link->player);
            if ($result) {
                $text = implode(EOL, $result);
                $this->channel->sendMessage($text);
            }
        });
    }
}
