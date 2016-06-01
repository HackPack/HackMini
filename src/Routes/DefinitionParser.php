<?hh // strict

namespace HackPack\HackMini\Routes;

use FredEmmott\DefinitionFinder\FileParser;
use FredEmmott\DefinitionFinder\ScannedFunction;
use FredEmmott\DefinitionFinder\ScannedParameter;
use FredEmmott\DefinitionFinder\ScannedBasicClass;
use FredEmmott\DefinitionFinder\ScannedTypehint;
use FredEmmott\DefinitionFinder\ScannedMethod;
use HackPack\HackMini\Message\Response;
use HackPack\HackMini\Message\Request;
use HackPack\HackMini\Message\RestMethod;

type ParseFailure = shape(
  'file' => string,
  'function' => string,
  'line' => ?int,
  'reason' => string,
);

final class DefinitionParser {
  public static function fromFileList(Vector<\SplFileInfo> $files): this {
    $functions = Vector {};
    $classes = Vector {};

    foreach ($files as $finfo) {
      if ($finfo->isFile() && $finfo->isReadable()) {
        $fileParser = FileParser::FromFile($finfo->getRealPath());
        $functions->addAll($fileParser->getFunctions());
        $classes->addAll($fileParser->getClasses());
      }
    }
    return new static($functions, $classes);
  }

  private Vector<ParseFailure> $failures = Vector {};
  private Vector<ParsedDefinition> $routes = Vector {};
  private Set<string> $patterns = Set {};

  public function __construct(
    \ConstVector<ScannedFunction> $functions,
    \ConstVector<ScannedBasicClass> $classes,
  ) {
    $this->parseFunctions($functions);
    $this->parseClasses($classes);
  }

  public function routes(): \ConstVector<ParsedDefinition> {
    return $this->routes;
  }

  public function failures(): \ConstVector<ParseFailure> {
    return $this->failures;
  }

  private function parseFunctions(
    \ConstVector<ScannedFunction> $functions,
  ): void {
    $functions->map(
      $function ==> {

        try {

          $routeAttribute = $function->getAttributes()->get('Route');
          if ($routeAttribute === null) {
            return;
          }

          list($verb, $pattern) = $this->checkRoute($routeAttribute);

          $this->checkParameters($function->getParameters());
          $middleware = $this->extractMiddleware(
            $function->getAttributes()->get('UseMiddleware'),
          );

          $this->routes->add(
            shape(
              'pattern' => $pattern,
              'verb' => $verb,
              'function' => $function->getName(),
              'middleware' => $middleware,
            ),
          );

        } catch (\UnexpectedValueException $e) {

          $pos = $function->getPosition();
          $this->failures->add(
            shape(
              'file' => $pos['filename'],
              'function' => $function->getName(),
              'line' => Shapes::idx($pos, 'line', null),
              'reason' => $e->getMessage(),
            ),
          );

        }
      },
    );
  }

  private function parseMethod(
    ScannedBasicClass $class,
    ScannedMethod $method,
  ): void {
    try {
      $routeAttribute = $method->getAttributes()->get('Route');
      if ($routeAttribute === null) {
        return;
      }

      list($verb, $pattern) = $this->checkRoute($routeAttribute);

      $this->checkParameters($method->getParameters());

      if (!$method->isStatic()) {
        throw new \UnexpectedValueException(
          'Route handler methods must be static.',
        );
      }

      $middleware = $this->extractMiddleware(
        $method->getAttributes()->get('UseMiddleware'),
      );

      $this->routes->add(
        shape(
          'verb' => $verb,
          'pattern' => $pattern,
          'method' => $method->getName(),
          'class' => $class->getName(),
          'middleware' => $middleware,
        ),
      );

    } catch (\UnexpectedValueException $e) {

      $pos = $method->getPosition();
      $this->failures->add(
        shape(
          'file' => $pos['filename'],
          'function' => $class->getName().'::'.$method->getName(),
          'line' => Shapes::idx($pos, 'line', null),
          'reason' => $e->getMessage(),
        ),
      );

    }
  }

  private function parseClasses(
    \ConstVector<ScannedBasicClass> $classes,
  ): void {
    foreach ($classes as $class) {
      foreach ($class->getMethods() as $method) {
        $this->parseMethod($class, $method);
      }
    }
  }

  private function checkRoute(
    Vector<mixed> $routeAttribute,
  ): (RestMethod, string) {

    if ($routeAttribute->count() < 1) {
      throw new \UnexpectedValueException(
        'You must specify a route pattern and optionally a REST verb.'.
        ' <<Route("GET", "/route/pattern")>>',
      );
    }

    if ($routeAttribute->count() === 1) {

      $pattern = (string) $routeAttribute->at(0);
      $verb = RestMethod::Any;
      $this->checkPattern($pattern, $verb);
      return tuple($verb, $pattern);

    }

    $pattern = (string) $routeAttribute->at(1);
    $verb = RestMethod::coerce(strtoupper((string) $routeAttribute->at(0)));
    if ($verb === null) {
      throw new \UnexpectedValueException(
        'Unknown REST verb '.(string) $routeAttribute->at(0),
      );
    }

    $this->checkPattern($pattern, $verb);
    return tuple($verb, $pattern);

  }

  private function checkParameters(
    \ConstVector<ScannedParameter> $parameters,
  ): void {
    $requiredParamNames =
      $parameters->filter($p ==> !$p->isOptional())->map(
        $p ==> {
          $name = $p->getTypehint()?->getTypeName();
          return $name === null ? '' : ltrim($name, '\\');
        },
      );

    if ($requiredParamNames->count() !== 1 ||
        $requiredParamNames->at(0) !== \FactoryContainer::class) {
      throw new \UnexpectedValueException(
        sprintf(
          'Command handlers must except exactly 1 parameter of type %s',
          \FactoryContainer::class,
        ),
      );
    }
  }

  private function checkPattern(string $pattern, RestMethod $verb): void {
    if ($this->patterns->contains($pattern.(string) $verb)) {
      $otherService =
        $this->routes->filter($s ==> $s['pattern'] === $pattern)->at(0);

      $otherFunction =
        Shapes::idx($otherService, 'function')
          ? Shapes::idx($otherService, 'function', '')
          : Shapes::idx($otherService, 'class', '').
          '::'.
          Shapes::idx($otherService, 'method');

      throw new \UnexpectedValueException(
        sprintf('Route %s already defined for %s', $pattern, $otherFunction),
      );
    }

    $this->patterns->add($pattern.$verb);
  }

  private function extractMiddleware(
    ?Vector<mixed> $middlewareAttribute,
  ): Vector<string> {
    if ($middlewareAttribute === null) {
      return Vector {};
    }
    return $middlewareAttribute->map($a ==> (string) $a);
  }
}
