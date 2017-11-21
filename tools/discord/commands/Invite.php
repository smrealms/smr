<?php

namespace App\Commands;

use Core\Command\Command;
use Core\Command\Parameters;

class Invite extends Command
{
    protected $name = 'invite';
    protected $description = 'Invite Autopilot to join your server!';

    public function index(Parameters $p)
    {
        catch_throws(function() {
            $msg = "Autopilot can be invited to join your server! Just click this link and select your server:\n<https://discordapp.com/oauth2/authorize?&client_id=380672395293229057&scope=bot&permissions=0>\n\nNOTE: you must have manager permissions to perform this action.";
            $this->channel->sendMessage($msg);
        });
    }
}
