<?hh // strict

use HackPack\HackMini\Command\Definition;

function commands() : Map<string, Definition>
{
    return Map{
        'user:create' => shape(
            'arguments' => Vector{ },
            'options' => Vector{ },
            'handler' => fun('HackPack\HackMini\Sample\createUserFromCli'),
        ),
    };
}
