<?hh // strict

namespace HackPack\HackMini\Contract;

use HackPack\HackMini\Middleware\Next;

type MiddlewareFactory<Trequest, Tresponse, Tresult> = (
  function(\FactoryContainer): Middleware<Trequest, Tresponse, Tresult>
);

interface Middleware<Trequest, Tresponse, Tresult> {
  public function handle(
    Trequest $req,
    Tresponse $rsp,
    Next<Trequest, Tresponse, Tresult> $next,
  ): Tresult;
}
