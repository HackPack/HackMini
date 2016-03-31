<?hh // strict

namespace HackPack\HackMini\Router;

use HackPack\HackMini\Message\Request;
use HackPack\HackMini\Message\Response;
use HackPack\HackMini\Contract\Middleware;

final class Web {
  <<Provides('WebRouter')>>
  public static function factory(\FactoryContainer $c): this {
    return new static(globalWebMiddleware(), $c);
  }

  public function __construct(
    private Vector<Middleware<Request, Response, Response>> $globalMiddleware,
    private \FactoryContainer $c,
  ) {}

  public function handle(Request $req, Response $rsp): Response {
    return $rsp->show('Working');
  }

}
