<?hh // strict

namespace HackPack\HackMini\Contract;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use HackPack\HackMini\Middleware\Next;

interface Middleware<Trequest as Request, Tresponse as Response>
{
    public function handle(
        Trequest$req,
        Tresponse $rsp,
        Next<Trequest, Tresponse> $next,
    ) : Tresponse;
}
