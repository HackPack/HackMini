<?hh // strict

namespace HackPack\HackMini\Router;

use HackPack\HackMini\Middleware\Cli\Handler;
use HackPack\HackMini\Command\Request;
use HackPack\HackMini\Command\UserInteraction;
use HackPack\HackMini\Command\ArgumentDefinition;
use HackPack\HackMini\Command\OptionDefinition;;
use HackPack\HackMini\Command\Definition;;

type RequestBuilder = (function(Vector<string>):Request);

class Command
{
    public function __construct(
        private \FactoryContainer $c,
        private Map<string, Definition> $commandList,
        private Vector<string> $globalMiddleware,
    )
    {
    }

    public function dispatch(Request $req, UserInteraction $interaction) : int
    {
        $commandDefinition = $this->commandList->get($req->name());

        if($commandDefinition === null) {
            // TODO: Show command not found error
            return 1;
        }

        $req = $req
            ->withArguments($commandDefinition['arguments'])
            ->withOptions($commandDefinition['options'])
            ;

        return 0;
    }
}
