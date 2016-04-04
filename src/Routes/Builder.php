<?hh // strict

namespace HackPack\HackMini\Routes;

use HackPack\HackMini\Message\RestMethod;

class Builder {
  const HEAD = <<<'Hack'
<?hh // strict

use HackPack\HackMini\Contract\MiddlewareFactory;
use HackPack\HackMini\Message\Request;
use HackPack\HackMini\Message\Response;
use HackPack\HackMini\Message\RestMethod;
use HackPack\HackMini\Middleware\Web\Handler;

function routes(
): Map<RestMethod,
Map<string,
shape(
  'handler' => Handler,
  'middleware' => Vector<MiddlewareFactory<Request, Response, Response>>,
)>> {
  return Map {
Hack;

  const FOOT = <<<'Hack'
  };
}
Hack;

  private Map<RestMethod, Vector<ParsedDefinition>>
    $definitionsByMethod = Map {};

  public function __construct(
    \ConstVector<ParsedDefinition> $routeDefinitions,
    private \ConstMap<string, string> $middlewareDefinitions,
  ) {
    $routeDefinitions->map(
      $d ==> {
        if ($this->definitionsByMethod->containsKey($d['verb'])) {
          $this->definitionsByMethod->at($d['verb'])->add($d);
          return;
        }
        $this->definitionsByMethod->set($d['verb'], Vector {$d});
      },
    );
  }

  public function render(): string {
    return implode(
      PHP_EOL,
      (Vector {self::HEAD})->addAll(
        $this->definitionsByMethod->mapWithKey(
          ($method, $definitions) ==> $this->renderMethod(
            $method,
            $definitions,
          ),
        ),
      )->add(self::FOOT),
    );
  }

  private function renderMethod(
    RestMethod $method,
    Vector<ParsedDefinition> $definition,
  ): string {
    $method = RestMethod::getNames()[$method];
    $start = <<<Hack
    RestMethod::{$method} => Map {
Hack;
    $end = <<<Hack
    },
Hack;

    return implode(
      PHP_EOL,
      (Vector {$start})
        ->addAll($definition->map($d ==> $this->renderDefinition($d)))
        ->add($end),
    );
  }

  private function renderDefinition(ParsedDefinition $definition): string {
    $function = Shapes::idx($definition, 'function');
    $class = Shapes::idx($definition, 'class');
    $method = Shapes::idx($definition, 'method');

    $handler =
      $function === null
        ? $this->renderMethodHandler($class, $method)
        : $this->renderFunctionHandler($function);

    $middleware = $this->renderMiddlewareStack($definition['middleware']);

    return<<<Hack
      '{$definition['pattern']}' => shape(
        'handler' => {$handler},
        'middleware' => {$middleware},
      ),
Hack;
  }

  private function renderMethodHandler(
    ?string $class,
    ?string $method,
  ): string {
    if ($class === null || $method === null) {
      throw new \UnexpectedValueException(
        'A route definition is lacking a fully qualified handler definition.',
      );
    }

    return "class_meth('{$class}', '{$method}')";
  }

  private function renderFunctionHandler(string $function): string {
    return "fun('{$function}')";
  }

  private function renderMiddlewareStack(
    \ConstVector<string> $middlewareNames,
  ): string {
    if ($middlewareNames->isEmpty()) {
      return 'Vector {}';
    }

    $factories =
      $middlewareNames->map(
        $name ==> {
          $factory = $this->middlewareDefinitions->get($name);
          if ($factory === null) {
            throw new \HackPack\HackMini\Middleware\UndefinedMiddleware(
              $name,
            );
          }
          return '          '.$factory.',';
        },
      );

    return implode(
      PHP_EOL,
      (Vector {})->add('Vector {')->addAll($factories)->add('        }'),
    );
  }
}
