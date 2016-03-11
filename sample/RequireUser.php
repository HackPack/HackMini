<?hh // strict

namespace HackPack\HackMini\Sample;

use FactoryContainer;
use HackPack\HackMini\Contract\Middleware;
use HackPack\HackMini\Message\Request;
use HackPack\HackMini\Message\Response;
use HackPack\HackMini\Middleware\Next;

final class RequireUser implements Middleware<Request, Response>
{
    <<Provides('RequireUser')>>
    public static function factory(FactoryContainer $c) : this
    {
        return new static($c->getAuth());
    }

    public function __construct(
        private Auth $auth,
    ) { }

    public function handle(
        Request $req,
        Response $rsp,
        Next<Request, Response> $next
    ) : Response
    {
        $me = $this->auth->extractUserFromRequest($req);
        if($me === null) {
            return $rsp->notAuthorized();
        }
        return $next($req, $rsp);
    }
}
