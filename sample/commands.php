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
        'user:show' => shape(
            'arguments' => Vector{ },
            'options' => Vector{ },
            'handler' => class_meth('HackPack\HackMini\Sample\HandlerClass', 'someCommand'),
        ),
    };
}
