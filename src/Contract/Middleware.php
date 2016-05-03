<?hh // strict

namespace HackPack\HackMini\Contract;

use HackPack\HackMini\Middleware\Next;
use FactoryContainer;

type MiddlewareFactory<Treq, Trsp, Tres> = (function(FactoryContainer): Middleware<Treq,
Trsp,
Tres>);

interface Middleware<Trequest, Tresponse, Tresult> {
  public function handle(
    Trequest $req,
    Tresponse $rsp,
    Next<Trequest, Tresponse, Tresult> $next,
  ): Tresult;
}
