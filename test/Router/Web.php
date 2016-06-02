<?hh // strict

namespace HackPack\HackMini\Test\Router;

use FactoryContainer;
use HackPack\HackUnit\Contract\Assert;
use HackPack\HackMini\Contract\MiddlewareFactory;
use HackPack\HackMini\Contract\Middleware;
use HackPack\HackMini\Message\HttpProtocolVersion;
use HackPack\HackMini\Message\Request;
use HackPack\HackMini\Message\Response;
use HackPack\HackMini\Message\RestMethod;
use HackPack\HackMini\Message\StringBody;
use HackPack\HackMini\Message\Uri;
use HackPack\HackMini\Middleware\Web\Handler;
use HackPack\HackMini\Middleware\Web\HandlerFactory;
use HackPack\HackMini\Router\MissingWebHandler;
use HackPack\HackMini\Router\Web;
use HackPack\HackMini\Test\Doubles\MiddlewareSpy;
use HackPack\HackMini\Test\Doubles\ClosureHandler;

class WebTest {
  private int $handlerRunCount = 0;
  private int $factoryRunCount = 0;

  private function buildRequest(string $path = '/'): Request {
    return new Request(
      HttpProtocolVersion::v11,
      RestMethod::Get,
      Uri::fromString($path),
      [], // headers
      [], // cookies
      new StringBody(''),
    );
  }

  private function buildHandlerFactory(): HandlerFactory {
    return ($c) ==> {
      $this->factoryRunCount++;
    return new ClosureHandler(($req, $rsp) ==> {
      $this->handlerRunCount++;
      return $rsp;
    });
    };
  }

  <<Test>>
  public function anyMethodIsUsed(Assert $assert): void {

    $router = new Web(
      Vector {}, // Global middleware
      Map {
        RestMethod::Any => Map {
          '/' => shape(
            'middleware' => Vector {},
            'factory' => $this->buildHandlerFactory(),
          ),
        },
      },
      new FactoryContainer(),
    );

    $assert->whenCalled(
      () ==> {
        $router->handle($this->buildRequest(), Response::factory());
      },
    )->willNotThrow();
    $assert->int($this->factoryRunCount)->eq(1);
    $assert->int($this->handlerRunCount)->eq(1);
  }

  <<Test>>
  public function regexPath(Assert $assert): void {

    $handlerFactory = $c ==> new ClosureHandler(($req, $rsp) ==> {
      $assert->container($req->pathGroups())
        ->containsOnly(['/abc/def', 'abc', 'def']);
      $this->handlerRunCount++;
      return $rsp;
    });

    $router = new Web(
      Vector {}, // Global middleware
      Map {
        RestMethod::Any => Map {
          '/([^/]*)/([^/]*)' => shape(
            'middleware' => Vector {},
            'factory' => $handlerFactory,
          ),
        },
      },
      new FactoryContainer(),
    );

    $assert->whenCalled(
      () ==> {
        $router->handle(
          $this->buildRequest('/abc/def'),
          Response::factory(),
        );
      },
    )->willNotThrow();
    $assert->int($this->handlerRunCount)->eq(1);
  }

  <<Test>>
  public function unknownMethodIsNotUsed(Assert $assert): void {

    $router = new Web(
      Vector {}, // Global middleware
      Map {
        RestMethod::Unknown => Map {
          '/' => shape(
            'middleware' => Vector {},
            'factory' => $this->buildHandlerFactory(),
          ),
        },
      },
      new FactoryContainer(),
    );

    $assert->whenCalled(
      () ==> {
        $router->handle($this->buildRequest(), Response::factory());
      },
    )->willThrowClass(MissingWebHandler::class);
    $assert->int($this->factoryRunCount)->eq(0);
    $assert->int($this->handlerRunCount)->eq(0);
  }

  <<Test>>
  public function specificMethodUsedFirst(Assert $assert): void {
    $methods = Set{};

    $getHandlerFactory = $c ==> new ClosureHandler(($req, $rsp) ==> {
      $methods->add('get');
      return $rsp;
    });

    $anyHandlerFactory = $c ==> new ClosureHandler(($req, $rsp) ==> {
      $methods->add('any');
      return $rsp;
    });

    $router = new Web(
      Vector {}, // Global middleware
      Map {
        RestMethod::Any => Map {
          '/' => shape(
            'middleware' => Vector {},
            'factory' => $anyHandlerFactory,
          ),
        },
        RestMethod::Get => Map {
          '/' => shape(
            'middleware' => Vector {},
            'factory' => $getHandlerFactory,
          ),
        },
      },
      new FactoryContainer(),
    );

    $assert->whenCalled(
      () ==> {
        $router->handle($this->buildRequest(), Response::factory());
      },
    )->willNotThrow();
    $assert->container($methods)->containsOnly(['get']);
  }

  <<Test>>
  public function oneHandlerIsCalled(Assert $assert): void {

    $router = new Web(
      Vector {}, // Global middleware
      Map {
        RestMethod::Get => Map {
          '/' => shape('middleware' => Vector {}, 'factory' => $this->buildHandlerFactory()),
          '/stuff' => shape(
            'middleware' => Vector {},
            'factory' => $this->buildHandlerFactory(),
          ),
        },
      },
      new \FactoryContainer(),
    );

    $assert->whenCalled(
      () ==> {
        $router->handle($this->buildRequest(), Response::factory());
      },
    )->willNotThrow();
    $assert->int($this->factoryRunCount)->eq(1);
    $assert->int($this->handlerRunCount)->eq(1);
  }

  <<Test>>
  public function correctParametersArePassed(Assert $assert): void {

    $request = $this->buildRequest();
    $response = Response::factory();
    $container = new FactoryContainer();

    $handlerFactory = $c ==> new ClosureHandler(($req, $rsp) ==> {
      // Make sure the handler was run
      $this->handlerRunCount++;
      // Response and container should be passed through
      $assert->mixed($rsp)->identicalTo($response);
      $assert->mixed($c)->identicalTo($container);
      return $rsp;
    });

    $router = new Web(
      Vector {}, // Global middleware
      Map {
        RestMethod::Get => Map {
          '/' => shape('middleware' => Vector {}, 'factory' => $handlerFactory),
        },
      },
      $container,
    );

    $assert->whenCalled(
      () ==> {
        $router->handle($request, $response);
      },
    )->willNotThrow();
    $assert->int($this->handlerRunCount)->eq(1);
  }

  <<Test>>
  public function missingHandlerWillThrow(Assert $assert): void {

    $request = $this->buildRequest();
    $response = Response::factory();
    $container = new \FactoryContainer();

    $router = new Web(
      Vector {}, // Global middleware
      Map {
        // Wrong method
        RestMethod::Post => Map {
          '/' => shape(
            'middleware' => Vector {},
            'factory' => $this->buildHandlerFactory(),
          ),
        },
        // Wrong path
        RestMethod::Get => Map {
          '/stuff' => shape(
            'middleware' => Vector {},
            'factory' => $this->buildHandlerFactory(),
          ),
        },
      },
      $container,
    );

    $assert->whenCalled(
      () ==> {
        $router->handle($request, $response);
      },
    )->willThrowClass(MissingWebHandler::class);
    $assert->int($this->factoryRunCount)->eq(0);
    $assert->int($this->handlerRunCount)->eq(0);
  }

  <<Test>>
  public function missingHandlerWillThrowIntoGlobalMiddleware(
    Assert $assert,
  ): void {

    $globalMiddleware = new MiddlewareSpy(
      ($req, $rsp, $next) ==> {
        $assert->whenCalled(
          () ==> {
            $next($req, $rsp);
          },
        )->willThrowClass(MissingWebHandler::class);
        return $rsp;
      },
    );

    $router = new Web(
      Vector {$globalMiddleware}, // Global middleware
      Map {
        RestMethod::Get => Map {
          '/stuff' => shape(
            'middleware' => Vector {},
            'factory' => $this->buildHandlerFactory(),
          ),
        },
      },
      new FactoryContainer(),
    );

    $assert->whenCalled(
      () ==> {
        $router->handle($this->buildRequest(), Response::factory());
      },
    )->willNotThrow();
    $assert->int($globalMiddleware->runCount())->eq(1);
    $assert->int($this->handlerRunCount)->eq(0);
  }

  <<Test>>
  public function middlewareAreRunInOrder(Assert $assert): void {
    $middlewareRun = Set {};
    $g1 = new MiddlewareSpy(
      ($req, $rsp, $next) ==> {
        $assert->container($middlewareRun)->isEmpty();
        $middlewareRun->add('g1');
        return $next($req, $rsp);
      },
    );
    $g2 = new MiddlewareSpy(
      ($req, $rsp, $next) ==> {
        $assert->container($middlewareRun)->containsOnly(['g1']);
        $middlewareRun->add('g2');
        return $next($req, $rsp);
      },
    );
    $l1 = new MiddlewareSpy(
      ($req, $rsp, $next) ==> {
        $assert->container($middlewareRun)->containsOnly(['g1', 'g2']);
        $middlewareRun->add('l1');
        return $next($req, $rsp);
      },
    );
    $l2 =
      new MiddlewareSpy(
        ($req, $rsp, $next) ==> {
          $assert->container($middlewareRun)
            ->containsOnly(['g1', 'g2', 'l1']);
          $middlewareRun->add('l2');
          return $next($req, $rsp);
        },
      );

    $router = new Web(
      Vector {$g1, $g2},
      Map {
        RestMethod::Get => Map {
          '/' => shape(
            'middleware' => Vector {($c) ==> $l1, ($c) ==> $l2},
            'factory' => $this->buildHandlerFactory(),
          ),
        },
      },
      new FactoryContainer(),
    );

    $assert->whenCalled(
      () ==> {
        $router->handle($this->buildRequest(), Response::factory());
      },
    )->willNotThrow();
    $assert->int($g1->runCount())->eq(1);
    $assert->int($g2->runCount())->eq(1);
    $assert->int($l1->runCount())->eq(1);
    $assert->int($l2->runCount())->eq(1);
    $assert->int($this->handlerRunCount)->eq(1);
  }

  <<Test>>
  public function middlewareMaySkip(Assert $assert): void {
    $middlewareRun = Set {};
    $m1 = new MiddlewareSpy(
      ($req, $rsp, $next) ==> {
        $assert->container($middlewareRun)->isEmpty();
        $middlewareRun->add(1);
        return $next($req, $rsp);
      },
    );
    $m2 = new MiddlewareSpy(
      ($req, $rsp, $next) ==> {
        $assert->container($middlewareRun)->containsOnly([1]);
        $middlewareRun->add(2);
        return $rsp;
      },
    );
    $m3 = new MiddlewareSpy(
      ($req, $rsp, $next) ==> {
        throw new \Exception('M3 should not run');
      },
    );

    $router = new Web(Vector {$m1, $m2, $m3}, Map {}, new FactoryContainer());

    $assert->whenCalled(
      () ==> {
        $router->handle($this->buildRequest(), Response::factory());
      },
    )->willNotThrow();
    $assert->int($m1->runCount())->eq(1);
    $assert->int($m2->runCount())->eq(1);
    $assert->int($m3->runCount())->eq(0);
    $assert->int($this->handlerRunCount)->eq(0);
  }
}
