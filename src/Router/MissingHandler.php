<?hh // strict

namespace HackPack\HackMini\Router;

use HackPack\HackMini\Message\Request;
use HackPack\HackMini\Message\Response;
use HackPack\HackMini\Middleware\Web\Handler;

class MissingWebHandler extends \Exception {
  public function __construct(public Request $req) {
    parent::__construct();
  }
}

class HandleMissingHandler implements Handler {
  public function handle (Request $req, Response $rsp): Response {
    throw new MissingWebHandler($req);
  }
}
