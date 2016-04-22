<?hh // strict

namespace HackPack\HackMini\Util;

use HackPack\HackMini\Contract\Middleware;
use HackPack\HackMini\Middleware\Next;

class ExceptionHandler<Treq, Trsp, Treturn> implements Middleware<Treq, Trsp, Treturn>
{
  public function handle(Treq $req, Trsp $rsp, Next<Treq, Trsp, Treturn> $next) : Treturn
  {
    try {
      return $next($req, $rsp);
    } catch (\Exception $e) {
      var_dump($e->getMessage());
      exit(1);
    }
  }

}
