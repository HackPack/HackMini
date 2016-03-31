<?hh // strict

namespace HackPack\HackMini;

use HackPack\HackMini\Message\Request;
use HackPack\HackMini\Message\Response;
use HackPack\HackMini\Contract\Middleware;

final class WebApp {
  <<Provides('WebApp')>>
  public static function factory(\FactoryContainer $c): this {
    return new static(
      $c->getServerRequest(),
      $c->getServerResponse(),
      $c->getWebRouter(),
    );
  }

  public function __construct(
    private Message\Request $req,
    private Message\Response $rsp,
    private Router\Web $router,
  ) {}

  public function run(): void {
    $response = $this->router->handle($this->req, $this->rsp);
    echo (string) $response->getBody();
  }
}
