<?hh // strict

namespace HackPack\HackMini\Middleware;

use FredEmmott\DefinitionFinder\FileParser;
use FredEmmott\DefinitionFinder\ScannedFunction;
use FredEmmott\DefinitionFinder\ScannedBasicClass;
use FredEmmott\DefinitionFinder\ScannedMethod;
use FredEmmott\DefinitionFinder\ScannedParameter;

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
  private Map<string, string> $middleware = Map {};

  public function __construct(
    \ConstVector<ScannedFunction> $functions,
    \ConstVector<ScannedBasicClass> $classes,
  ) {
    $this->parseFunctions($functions);
    $this->parseClasses($classes);
  }

  public function middleware(): \ConstMap<string, string> {
    return $this->middleware;
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

          $attributes = $function->getAttributes();
          $name = $this->checkName($attributes->get('ProvideMiddleware'));
          if ($name === null) {
            return;
          }

          $this->checkParameters($function->getParameters());

          $functionName = $function->getName();
          $this->middleware->set($name, "fun('{$functionName}')");

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
      $attributes = $method->getAttributes();
      $name = $this->checkName($attributes->get('ProvideMiddleware'));
      if ($name === null) {
        return;
      }

      if (!$method->isStatic()) {
        throw new \UnexpectedValueException(
          'Middleware provider methods must be static.',
        );
      }

      $this->checkParameters($method->getParameters());

      $className = $class->getName();
      $methodName = $method->getName();
      $this->middleware
        ->set($name, "class_meth('{$className}', '{$methodName}')");

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

  private function checkName(?Vector<mixed> $name): ?string {
    if ($name === null) {
      return null;
    }

    if ($name->isEmpty()) {
      throw new \UnexpectedValueException(
        'You must name your middleware: <<ProvideMiddleware(\'name\')>>',
      );
    }

    $name = $name->at(0);

    if (!is_string($name)) {
      throw new \UnexpectedValueException(
        'Middleware names must be strings.',
      );
    }

    if ($this->middleware->containsKey($name)) {
      throw new \UnexpectedValueException(
        'There already exists a middleware named '.$name,
      );
    }
    return $name;
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
        $requiredParamNames->at(0) !== 'FactoryContainer') {
      throw new \UnexpectedValueException(
        'Command handlers must except exactly 1 parameter of type \FactoryContainer.',
      );
    }
  }
}
