<?hh // strict

namespace HackPack\HackMini\Sample;

use HackPack\HackMini\Contract\Middleware;
use HackPack\HackMini\Command\Request;
use HackPack\HackMini\Command\UserInteraction;
use HackPack\HackMini\Middleware\Next;

final class ShowColors implements Middleware<Request, UserInteraction, int>
{

    <<ProvideMiddleware('showcolors')>>
    public static function factory(\FactoryContainer $c) : this
    {
        return new static();
    }

    public function handle(
        Request $req,
        UserInteraction $interaction,
        Next<Request, UserInteraction, int> $next,
    ) : int
    {
        $interaction->showLine('Exiting early');
        return 1;
    }
}
