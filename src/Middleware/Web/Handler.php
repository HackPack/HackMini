<?hh // strict

namespace HackPack\HackMini\Middleware\Web;

use FactoryContainer;
use HackPack\HackMini\Message\Request;
use HackPack\HackMini\Message\Response;

type HandlerFactory = (function(FactoryContainer):Handler);

interface Handler {
  public function handle(Request $req, Response $rsp): Response;
}
