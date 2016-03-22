<?hh // strict

namespace HackPack\HackMini\Command;

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

final class DefinitionParser
{
    public static function fromFileList(Vector<\SplFileInfo> $files) : this
    {
        $functions = Vector{};
        $classes = Vector{};

        foreach($files as $finfo) {
            if($finfo->isFile() && $finfo->isReadable()) {
                $fileParser = FileParser::FromFile($finfo->getRealPath());
                $functions->addAll($fileParser->getFunctions());
                $classes->addAll($fileParser->getClasses());
            }
        }
        return new static($functions, $classes);
    }

    private Vector<ParseFailure> $failures = Vector{};
    private Vector<ParsedDefinition> $commands = Vector{};
    private Set<string> $names = Set{};

    public function __construct(
        \ConstVector<ScannedFunction> $functions,
        \ConstVector<ScannedBasicClass> $classes,
    )
    {
        $this->parseFunctions($functions);
        $this->parseClasses($classes);
    }

    public function commands() : \ConstVector<ParsedDefinition>
    {
        return $this->commands;
    }

    public function failures() : \ConstVector<ParseFailure>
    {
        return $this->failures;
    }

    private function parseFunctions(\ConstVector<ScannedFunction> $functions) : void
    {
        array_walk($functions->toArray(), $function ==> {

            try {

                $attributes = $function->getAttributes();
                $commandName = $this->checkCommandName($attributes->get('Command'));
                if($commandName === null) {
                    return;
                }
                $middleware = $this->checkMiddleware($attributes->get('UseMiddleware'));

                $this->checkParameters($function->getParameters());
                $this->checkReturnType($function->getReturnType());

                $this->names->clear();

                $this->commands->add(shape(
                    'name' => $commandName,
                    'function' => $function->getName(),
                    'options' => $this->defineOptions(
                        $commandName,
                        $function->getAttributes()->get('Options')
                    ),
                    'arguments' => $this->defineArguments(
                        $commandName,
                        $function->getAttributes()->get('Arguments')
                    ),
                    'middleware' => $middleware,
                ));

            } catch (\UnexpectedValueException $e) {

                $pos = $function->getPosition();
                $this->failures->add(shape(
                    'file' => $pos['filename'],
                    'function' => $function->getName(),
                    'line' => Shapes::idx($pos, 'line', null),
                    'reason' => $e->getMessage(),
                ));

            }
        });
    }

    private function parseMethod(ScannedBasicClass $class, ScannedMethod $method) : void
    {
        try {
            $attributes = $method->getAttributes();
            $commandName = $this->checkCommandName($attributes->get('Command'));
            if($commandName === null) {
                return;
            }

            if( ! $method->isStatic()) {
                throw new \UnexpectedValueException('Command methods must be static.');
            }

            $middleware = $this->checkMiddleware($attributes->get('UseMiddleware'));
            $this->checkParameters($method->getParameters());
            $this->checkReturnType($method->getReturnType());

            $this->names->clear();

            $this->commands->add(shape(
                'name' => $commandName,
                'method' => $method->getName(),
                'class' => $class->getName(),
                'options' => $this->defineOptions(
                    $commandName,
                    $method->getAttributes()->get('Options')
                ),
                'arguments' => $this->defineArguments(
                    $commandName,
                    $method->getAttributes()->get('Arguments')
                ),
                'middleware' => $middleware,
            ));

        } catch (\UnexpectedValueException $e) {

            $pos = $method->getPosition();
            $this->failures->add(shape(
                'file' => $pos['filename'],
                'function' => $class->getName() . '::' . $method->getName(),
                'line' => Shapes::idx($pos, 'line', null),
                'reason' => $e->getMessage(),
            ));

        }
    }

    private function parseClasses(\ConstVector<ScannedBasicClass> $classes) : void
    {
        foreach($classes as $class) {
            foreach($class->getMethods() as $method) {
                $this->parseMethod($class, $method);
            }
        }
    }

    private function checkCommandName(?Vector<mixed> $commandAttribute) : ?string
    {
        if($commandAttribute === null) {
            return null;
        }

        if($commandAttribute->isEmpty()) {
            throw new \UnexpectedValueException(
                 'The command must be given a name, e.g., <<Command(\'mycommand\')>>'
             );
        }

        $name = (string)$commandAttribute->at(0);

        $match = [];
        if(preg_match('/([ |=])/', $name, $match)) {
            $invalidChar = $match[1];
            $msg = ($invalidChar === ' ') ?
                'Command names may not contain spaces.' :
                'Command names may not contain "' . $invalidChar . '" characters.';

            throw new \UnexpectedValueException($msg);
        }

        return $name;
    }

    private function defineOptions(
        string $commandName,
        ?Vector<mixed> $options
    ) : \ConstVector<OptionDefinition>
    {
        if($options === null) {
            return Vector{};
        }

        return $options->map($option ==> {

            if(!is_string($option)) {
                throw new \UnexpectedValueException(
                    'Option names must be strings.'
                );
            }

            $definition = $this->parseOptionName($commandName, $option);

            $match = [];
            if(preg_match('/([ |=])/', $definition['name'], $match)) {
                $invalidChar = $match[1];
                $msg = ($invalidChar === ' ') ?
                    'Option names may not contain spaces.' :
                    'Option names may not contain "' . $invalidChar . '" characters.';

                throw new \UnexpectedValueException($msg);
            }

            return $definition;

        });
    }

    private function parseOptionName(
        string $commandName,
        string $name,
    ) : shape(
        'name' => string,
        'alias' => ?string,
        'value required' => bool,
        'default' => ?string,
    )
    {
        if($this->optionHasAlias($name)) {
            $parts = explode('|', $name, 2);
            if(count($parts) < 2) {
                throw new \UnexpectedValueException('All options must have a name.');
            }
            $alias = $parts[0];
            $name = $parts[1];

            if(strlen($alias) > 1) {
                throw new \UnexpectedValueException('The alias of an option must be one character long.');
            }

        } else {
            $alias = null;
        }

        if(strpos($name, '=') === false) {
            $default = null;
            $valueRequired = false;
        } else {
            $valueRequired = true;
            $parts = explode('=', $name, 2);
            $name = $parts[0];
            $default = $parts[1] === '' ? null : $parts[1];
        }

        $definition = shape(
            'name' => $name,
            'value required' => $valueRequired,
        );

        $this->checkName($commandName, $name, $name !== $alias);

        if($alias !== null) {
            $this->checkName($commandName, $alias, true);
            $definition['alias'] = $alias;
        }

        if($default !== null) {
            $definition['default'] = $default;
        }

        return $definition;
    }

    private function optionHasAlias(string $name) : bool
    {
        $pipepos = strpos($name, '|');

        if($pipepos === false) {
             return false;
        }

        $equalpos = strpos($name, '=');
        if($equalpos === false) {
            return true;
        }

        return $pipepos < $equalpos;
    }

    private function defineArguments(
        string $commandName,
        ?Vector<mixed> $arguments,
    ) : \ConstVector<ArgumentDefinition>
    {
        if($arguments === null) {
             return Vector{};
        }

        $required = Vector{true};
        return $arguments->map($a ==> {

            if(!is_string($a)) {
                throw new \UnexpectedValueException('The definition of an argument must be a string.');
            }

            if(strpos($a, '=') === false) {
                if($required->at(0) === false) {
                     throw new \UnexpectedValueException('All arguments with default values must be at the end of the list.');
                }
                $this->checkName($commandName, $a, true);
                return shape('name' => $a);
            }

            $required->set(0, false);

            list($name, $default) = explode('=', $a, 2);

            return shape(
                'name' => $name,
                'default' => $default,
            );
        });
    }

    private function checkParameters(\ConstVector<ScannedParameter> $parameters) : void
    {
        $requiredParamNames = $parameters
            ->filter($p ==> ! $p->isOptional())
            ->map($p ==> {
                $name = $p->getTypehint()?->getTypeName();
                return $name === null ? '' : ltrim($name, '\\');
            })
            ;

        if(
            $requiredParamNames->count() !== 3 ||
            $requiredParamNames->at(0) !== \FactoryContainer::class ||
            $requiredParamNames->at(1) !== Request::class ||
            $requiredParamNames->at(2) !== UserInteraction::class
        ) {
            throw new \UnexpectedValueException(
                'Command handlers must except exactly 3 parameters:' . PHP_EOL .
                \FactoryContainer::class . PHP_EOL .
                Request::class . PHP_EOL .
                UserInteraction::class
            );
        }
    }

    private function checkReturnType(?ScannedTypehint $returnType) : void
    {
        if(
            $returnType === null ||
            $returnType->getTypeName() !== 'int'
        ) {
            throw new \UnexpectedValueException(
                 'Command handlers must have a return type of "int"'
            );
        }
    }

    private function checkName(string $commandName, string $name, bool $add) : void
    {
        if($this->names->contains($name)) {
            throw new \UnexpectedValueException(sprintf(
                'Command "%s" contains two arguments/options named "%s".  Option/argument names must be unique.',
                $commandName,
                $name,
            ));
        }

        if($add) {
            $this->names->add($name);
        }
    }

    private function checkMiddleware(?Vector<mixed> $stack) : Vector<string>
    {
        if($stack === null) {
            return Vector{};
        }

        $out = Vector{};
        foreach($stack as $value) {
            if(!is_string($value)) {
                throw new \UnexpectedValueException(
                    'Middleware names must be strings',
                );
            }
            $out->add($value);
        }
        return $out;
    }
}
