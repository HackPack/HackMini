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

type ParsedDefinition = shape(
    'name' => string,
    'function' => ?string,
    'class' => ?string,
    'method' => ?string,
    'arguments' => \ConstVector<string>,
    'options' => \ConstVector<OptionDefinition>,
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

                $commandName = $this->checkCommandName($function->getAttributes()->get('Command'));
                if($commandName === null) {
                    return;
                }

                $this->checkParameters($function->getParameters());
                $this->checkReturnType($function->getReturnType());

                $this->commands->add(shape(
                    'name' => $commandName,
                    'function' => $function->getName(),
                    'options' => $this->defineOptions($function->getAttributes()->get('Options')),
                    'arguments' => $this->defineArguments($function->getAttributes()->get('Arguments')),
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
            $commandName = $this->checkCommandName($method->getAttributes()->get('Command'));
            if($commandName === null) {
                return;
            }

            if( ! $method->isStatic()) {
                throw new \UnexpectedValueException('Command methods must be static.');
            }

            $this->checkParameters($method->getParameters());
            $this->checkReturnType($method->getReturnType());

            $this->commands->add(shape(
                'name' => $commandName,
                'method' => $method->getName(),
                'class' => $class->getName(),
                'options' => $this->defineOptions($method->getAttributes()->get('Options')),
                'arguments' => $this->defineArguments($method->getAttributes()->get('Arguments')),
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

    private function defineOptions(?Vector<mixed> $options) : \ConstVector<OptionDefinition>
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

            $valueRequired = false;
            if(substr($option, -1) === '=') {
                $valueRequired = true;
                $option = substr($option, 0, -1);
            }

            $definition = $this->parseOptionName($option);
            $definition['value required'] = $valueRequired;

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
        string $name,
    ) : shape(
        'name' => string,
        'alias' => ?string,
    )
    {
        $parts = explode('|', $name, 2);

        if(count($parts) === 1) {
            return shape(
                'name' => $parts[0],
                'alias' => strlen($parts[0]) === 1 ? $parts[0] : null,
            );
        }

        if(strlen($parts[0]) > 1) {
             throw new \UnexpectedValueException('Option aliases must be one character');
        }

        return shape(
            'name' => $parts[1],
            'alias' => $parts[0],
        );
    }

    private function defineArguments(?Vector<mixed> $arguments) : \ConstVector<string>
    {
        if($arguments === null) {
             return Vector{};
        }

        return $arguments->map($a ==> {
            if(is_string($a)) {
                return $a;
            }
            throw new \UnexpectedValueException('Argument names must be strings.');
        });
    }

    private function checkParameters(\ConstVector<ScannedParameter> $parameters) : void
    {
        $requiredParamNames = $parameters
            ->filter($p ==> ! $p->isOptional())
            ->map($p ==> {
                $name = $p->getTypehint()?->getTypeName();
                return $name === null ? '' : $name;
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
}
