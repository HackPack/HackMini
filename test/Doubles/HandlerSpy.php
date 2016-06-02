<?hh // strict

namespace HackPack\HackMini\Test\Doubles;

use HackPack\HackMini\Middleware\Web\Handler;
use HackPack\HackMini\Message\Request;
use HackPack\HackMini\Message\Response;

class ClosureHandler implements Handler {
  private (function(Request, Response):Response) $handler;

  public function __construct((function(Request, Response):Response) $handler) {
    $this->handler = $handler;
  }

  public function handle(Request $req, Response $rsp): Response {
    $h = $this->handler;
    return $h($req, $rsp);
  }
}
