<?hh // strict

namespace HackPack\HackMini\Router;

use HackPack\HackMini\Contract\MiddlewareFactory;
use HackPack\HackMini\Contract\Middleware;
use HackPack\HackMini\Message\Request;
use HackPack\HackMini\Message\Response;
use HackPack\HackMini\Message\RestMethod;
use HackPack\HackMini\Middleware\Web\Handler;

final class Web {

  private Vector<Middleware<Request, Response, Response>> $stack = Vector {};
  private array<string> $pathGroups = [];

  <<Provides('WebRouter')>>
  public static function factory(\FactoryContainer $c): this {
    return new static(globalWebMiddleware(), routes(), $c);
  }

  public function __construct(
    Vector<Middleware<Request, Response, Response>> $globalMiddleware,
    private Map<RestMethod,
    Map<string,
    shape(
      'handler' => Handler,
      'middleware' => Vector<MiddlewareFactory<Request,
      Response,
      Response>>,
    )>> $handlers,
    private \FactoryContainer $c,
  ) {
    $this->stack->addAll($globalMiddleware);
  }

  public function handle(Request $req, Response $rsp): Response {
    $handler = $this->findHandler($req);
    $this->stack->addAll($handler['middleware']->map($m ==> $m($this->c)));
    return $this->runStack(
      $handler['handler'],
      $req->withPathGroups($this->pathGroups),
      $rsp,
    );
  }

  private function findHandler(
    Request $req,
  ): shape(
    'handler' => Handler,
    'middleware' => Vector<MiddlewareFactory<Request, Response, Response>>,
  ) {
    // Look for the specific request method
    $handler = $this->match($req, $this->handlers->get($req->getMethod()));

    // If not found, look for handler that can handle any request
    if ($handler === null && $req->getMethod() !== RestMethod::Any) {
      $handler = $this->match($req, $this->handlers->get(RestMethod::Any));
    }

    // If no handlers were found, throw and exception.
    if ($handler === null) {
      $handler = shape(
        'middleware' => Vector {},
        'handler' => ($c, $req, $rsp) ==> {
          throw new MissingWebHandler($req);
        },
      );
    }
    return $handler;
  }

  private function match(
    Request $req,
    ?Map<string,
    shape(
      'handler' => Handler,
      'middleware' => Vector<MiddlewareFactory<Request,
      Response,
      Response>>,
    )> $handlerList,
  ): ?shape(
    'handler' => Handler,
    'middleware' => Vector<MiddlewareFactory<Request, Response, Response>>,
  ) {
    if ($handlerList === null) {
      return null;
    }

    // TODO: use some form of nikic's fastroute
    foreach ($handlerList as $pattern => $handler) {
      if (preg_match(
            "#{$pattern}#",
            $req->getUri()->getPath(),
            $this->pathGroups,
          )) {
        return $handler;
      }
    }
    return null;
  }

  private function runStack(
    Handler $handler,
    Request $req,
    Response $rsp,
  ): Response {
    if ($this->stack->isEmpty()) {
      return $handler($this->c, $req, $rsp);
    }

    $current = array_shift($this->stack);

    $next = ($req, $rsp) ==> {
      return $this->runStack($handler, $req, $rsp);
    };

    return $current->handle($req, $rsp, $next);
  }

}
