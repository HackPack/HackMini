<?hh // strict

namespace HackPack\HackMini\Command;

final class Builder {
  const string HEAD = <<<'Hack'
<?hh // strict

/**
 * This file is generated by invoking `hackmini commands:build`.
 * Do not manually edit this file.
 */

use HackPack\HackMini\Command\Definition;

function commands() : Map<string, Definition>
{
    return Map{
Hack;

  const string FOOT = <<<'Hack'
    };
}
Hack;

  public function __construct(
    private \ConstVector<ParsedDefinition> $definitions,
    private \ConstMap<string, string> $middleware,
  ) {}

  public function render(): string {
    return implode(
      PHP_EOL,
      (Vector {self::HEAD})->addAll(
        $this->definitions->map($d ==> $this->renderDefinition($d)),
      )->add(self::FOOT),
    );
  }

  private function renderDefinition(ParsedDefinition $definition): string {
    $name = $definition['name'];
    $options = $this->renderOptions($definition['options']);
    $arguments = $this->renderArguments($definition['arguments']);

    $handler = $this->renderHandler($definition);

    $middleware = $this->renderMiddleware($definition['middleware']);

    return<<<Hack
        '{$name}' => shape(
            'arguments' => {$arguments},
            'options' => {$options},
            'handler' => {$handler},
            'middleware' => {$middleware},
        ),
Hack;
  }

  private function renderOptions(
    \ConstVector<OptionDefinition> $definitions,
  ): string {
    if ($definitions->isEmpty()) {
      return 'Vector{}';
    }

    return implode(
      PHP_EOL,
      (Vector {'Vector{'})
        ->addAll($definitions->map($d ==> $this->renderOption($d)))
        ->add('            }'),
    );
  }

  private function renderOption(OptionDefinition $definition): string {
    $name = $definition['name'];
    $alias = Shapes::idx($definition, 'alias');
    $default = Shapes::idx($definition, 'default');
    $valueRequired = var_export($definition['value required'], true);

    $lines = Vector {"'name' => '{$name}',"};

    if ($alias !== null) {
      $lines->add("'alias' => '{$alias}',");
    }

    $lines->add("'value required' => {$valueRequired},");

    if ($default !== null) {
      $lines->add("'default' => '{$default}',");
    }

    return implode(
      PHP_EOL,
      (Vector {'                shape('})
        ->addAll($lines->map($l ==> '                    '.$l))
        ->add('                ),'),
    );
  }

  private function renderArguments(
    \ConstVector<ArgumentDefinition> $arguments,
  ): string {
    if ($arguments->isEmpty()) {
      return 'Vector{}';
    }

    return implode(
      PHP_EOL,
      (Vector {'Vector{'})
        ->addAll($arguments->map($d ==> $this->renderArgument($d)))
        ->add('            }'),
    );
  }

  private function renderArgument(ArgumentDefinition $definition): string {
    $name = $definition['name'];
    $default = Shapes::idx($definition, 'default');

    $lines = Vector {"'name' => '{$name}',"};

    if ($default !== null) {
      $lines->add("'default' => '{$default}',");
    }

    return implode(
      PHP_EOL,
      (Vector {'                shape('})
        ->addAll($lines->map($l ==> '                    '.$l))
        ->add('                ),'),
    );
  }

  private function renderHandler(ParsedDefinition $definition): string {
    if (Shapes::idx($definition, 'function') === null &&
        (Shapes::idx($definition, 'class') === null ||
         Shapes::idx($definition, 'method') === null)) {
      throw new \UnexpectedValueException(
        'Either a function name or a class and method name are needed '.
        'to render a command handler.',
      );
    }

    return
      Shapes::idx($definition, 'function') === null
        ? $this->renderMethodCommand(
          Shapes::idx($definition, 'class', ''),
          Shapes::idx($definition, 'method', ''),
        )
        : $this->renderFunctionCommand(
          Shapes::idx($definition, 'function', ''),
        );
  }

  private function renderFunctionCommand(string $function): string {
    return "fun('{$function}')";
  }

  private function renderMethodCommand(string $class, string $method): string {
    return "class_meth({$class}::class, '{$method}')";
  }

  private function renderMiddleware(\ConstVector<string> $list): string {
    if ($list->isEmpty()) {
      return 'Vector{}';
    }

    $list = $list->map(
      $item ==> {
        $code = $this->middleware->get($item);
        if ($code === null) {
          throw new \UnexpectedValueException('Unknown middleware '.$item);
        }
        return $code;
      },
    );

    return implode(
      PHP_EOL.'            ',
      (Vector {'Vector{'})
        ->addAll($list->map($item ==> '    '.$item.','))
        ->add('}'),
    );
  }
}
