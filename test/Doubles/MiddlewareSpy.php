<?hh // strict

namespace HackPack\HackMini\Test\Doubles;

use HackPack\HackMini\Contract\Middleware;
use HackPack\HackMini\Middleware\Next;

class MiddlewareSpy<Treq, Trsp, Tret>
  implements Middleware<Treq, Trsp, Tret> {
  private int $runCount = 0;

  private (function(Treq, Trsp, Next<Treq, Trsp, Tret>): Tret) $handle;

  public function __construct(
    (function(Treq, Trsp, Next<Treq, Trsp, Tret>): Tret) $handle,
  ) {
    $this->handle = $handle;
  }

  public function handle(
    Treq $req,
    Trsp $rsp,
    Next<Treq, Trsp, Tret> $next,
  ): Tret {
    $this->runCount++;
    $handle = $this->handle;
    return $handle($req, $rsp, $next);
  }

  public function runCount(): int {
    return $this->runCount;
  }

  public function wasRun(): bool {
    return $this->runCount > 0;
  }
}
