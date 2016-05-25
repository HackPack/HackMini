<?hh // strict

namespace HackPack\HackMini;

use HackPack\HackMini\Message\Request;
use HackPack\HackMini\Message\Response;
use HackPack\HackMini\Message\Cookie;
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

    foreach ($response->getCookies() as $cookie) {
      $c = $this->normalizeCookie($cookie);
      setcookie(
        $c['name'],
        $c['payload'],
        $c['expires'],
        $c['path'],
        $c['domain'],
        $c['secure'],
        $c['http only'],
      );
    }

    foreach($response->getHeaders() as $name => $value) {
      header("$name: $value");
    }

    http_response_code($response->getStatusCode());

    // TODO: actually stream this
    echo (string)$response->getBody();
  }

  private function normalizeCookie(
    Cookie $cookie,
  ): shape(
    'name' => string,
    'payload' => string,
    'expires' => int,
    'path' => string,
    'domain' => string,
    'secure' => bool,
    'http only' => bool,
  ) {
    $expires = Shapes::idx($cookie, 'expires', null);
    return shape(
      'name' => $cookie['name'],
      'payload' => $cookie['payload'],
      'expires' => $expires === null ? 0 : $expires->getTimestamp(),
      'path' => Shapes::idx($cookie, 'path', ''),
      'domain' => Shapes::idx($cookie, 'domain', ''),
      'secure' => Shapes::idx($cookie, 'secure', true),
      'http only' => Shapes::idx($cookie, 'http only', true),
    );
  }

}
