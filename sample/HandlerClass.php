<?hh // strict

namespace HackPack\HackMini\Sample;

use HackPack\HackMini\Command\Request;
use HackPack\HackMini\Command\UserInteraction;

class HandlerClass
{
    <<Command('some-command')>>
    public static function someCommand(\FactoryContainer $c, Request $req, UserInteraction $interaction) : int
    {
        return 1;
    }
}
