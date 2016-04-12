<?hh // strict

namespace HackPack\HackMini\Container;

use FredEmmott\DefinitionFinder\FileParser;
use FredEmmott\DefinitionFinder\ScannedFunction;
use FredEmmott\DefinitionFinder\ScannedParameter;
use FredEmmott\DefinitionFinder\ScannedBasicClass;
use FredEmmott\DefinitionFinder\ScannedTypehint;
use FredEmmott\DefinitionFinder\ScannedMethod;

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
  private Vector<ParsedDefinition> $services = Vector {};
  private Set<string> $names = Set {};

  public function __construct(
    \ConstVector<ScannedFunction> $functions,
    \ConstVector<ScannedBasicClass> $classes,
  ) {
    $this->parseFunctions($functions);
    $this->parseClasses($classes);
  }

  public function services(): \ConstVector<ParsedDefinition> {
    return $this->services;
  }

  public function failures(): \ConstVector<ParseFailure> {
    return $this->failures;
  }

  private function parseFunctions(
    \ConstVector<ScannedFunction> $functions,
  ): void {
    array_walk(
      $functions->toArray(),
      $function ==> {

        try {

          $serviceName = $this->checkServiceName(
            $function->getAttributes()->get('Provides'),
          );
          if ($serviceName === null) {
            return;
          }

          $this->checkParameters($function->getParameters());
          $return = $function->getReturnType()?->getTypeName();
          if ($return === null) {
            throw new \UnexpectedValueException(
              'You must specify a return value.',
            );
          }

          $this->services->add(
            shape(
              'name' => $serviceName,
              'function' => $function->getName(),
              'return' => $return,
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
      $serviceName =
        $this->checkServiceName($method->getAttributes()->get('Provides'));
      if ($serviceName === null) {
        return;
      }

      if (!$method->isStatic()) {
        throw new \UnexpectedValueException(
          'Command methods must be static.',
        );
      }

      $this->checkParameters($method->getParameters());
      $return = $method->getReturnType()?->getTypeName();
      if ($return === null) {
        throw new \UnexpectedValueException(
          'You must specify a return value.',
        );
      }

      if ($return === 'this') {
        $return = $class->getName();
      }

      $this->services->add(
        shape(
          'name' => $serviceName,
          'return' => $return,
          'method' => $method->getName(),
          'class' => $class->getName(),
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

  private function checkServiceName(
    ?Vector<mixed> $serviceAttribute,
  ): ?string {
    if ($serviceAttribute === null) {
      return null;
    }

    if ($serviceAttribute->isEmpty()) {
      throw new \UnexpectedValueException(
        'The service must be given a name, e.g., <<service(\'myservice\')>>',
      );
    }

    $name = (string) $serviceAttribute->at(0);

    if (!preg_match('/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/', $name)) {
      throw new \UnexpectedValueException(
        'Service names must be valid function names.',
      );
    }

    $this->checkName($name);

    return $name;
  }

  private function checkParameters(
    \ConstVector<ScannedParameter> $parameters,
  ): void {
    $requiredParamNames = $parameters->filter($p ==> !$p->isOptional())->map(
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

  private function checkName(string $name): void {
    if ($this->names->contains($name)) {
      $otherService =
        $this->services->filter($s ==> $s['name'] === $name)->at(0);

      $otherFunction =
        Shapes::idx($otherService, 'function')
          ? Shapes::idx($otherService, 'function', '')
          : Shapes::idx($otherService, 'class', '').
          '::'.
          Shapes::idx($otherService, 'method');

      throw new \UnexpectedValueException(
        sprintf(
          'Service name %s already defined for %s',
          $name,
          $otherFunction,
        ),
      );
    }

    $this->names->add($name);
  }
}
