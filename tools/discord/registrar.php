<?php

// Commands register

return [
    // All commands are registered here
    'commands' => [
        // All built-in commands are registered here
        Core\Base\Commands\Info\Help::class,
        Core\Base\Commands\User\UserInfo::class,

        // Register any of your custom commands here
        App\Commands\Turns::class,
        App\Commands\Money::class,
        App\Commands\Game::class,
        App\Commands\Invite::class,
    ],

    // All command aliases are registered here
    'aliases' => [
        // All built-in command aliases are registered here

        // Register any of your custom command aliases here
    ],

    // All console commands are registered here
    'console' => [
        // All built-in console commands are registered here
        Core\Console\Alias\Create::class,
        Core\Console\Alias\Delete::class,
        Core\Console\ClearLogs::class,
        Core\Console\Command\Create::class,
        Core\Console\Command\Delete::class,
        Core\Console\ConsoleCommand\Create::class,
        Core\Console\ConsoleCommand\Delete::class,
        Core\Console\Run::class,

        // Register any of your custom console commands here
    ],
];
