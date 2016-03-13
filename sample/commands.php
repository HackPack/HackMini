<?hh // strict

use HackPack\HackMini\Command\Definition;

function commands() : Map<string, Definition>
{
    return Map{
        'user:create' => shape(
            'arguments' => Vector{
                shape(
                    'name' => 'name',
                    'default' => 'default'
                ),
            },
            'options' => Vector{
                shape(
                    'name' => 'title',
                    'alias' => 't',
                    'value required' => true,
                    'default' => 'default',
                ),
            },
            'handler' => fun('HackPack\HackMini\Sample\createUserFromCli'),
        ),
        'some-command' => shape(
            'arguments' => Vector{},
            'options' => Vector{},
            'handler' => class_meth(HackPack\HackMini\Sample\HandlerClass::class, 'someCommand'),
        ),
    };
}
