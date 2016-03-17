<?hh // strict

namespace HackPack\HackMini\Command;

class Parser
{
    private \ConstVector<ArgumentDefinition> $argumentDefinitions = Vector{};
    private \ConstVector<OptionDefinition> $optionDefinitions = Vector{};
    private array<string> $stack = [];
    private Map<string, Vector<string>> $input = Map{};
    private Vector<string> $unnamedArguments = Vector{};
    private int $argsFound = 0;

    public function parse(
        \ConstVector<string> $input,
        \ConstVector<ArgumentDefinition> $argumentDefinitions,
        \ConstVector<OptionDefinition> $optionDefinitions,
    ) : shape(
        'input' => \ConstMap<string, \ConstVector<string>>,
        'unnamed arguments' => \ConstVector<string>,
    ) {
        $this->stack = $input->toArray();
        $this->argumentDefinitions = $argumentDefinitions;
        $this->optionDefinitions = $optionDefinitions;
        $this->input->clear();
        $this->unnamedArguments->clear();
        $this->argsFound = 0;

        while(true) {
            $arg = $this->shift();
            if($arg === null) {
                break;
            }

            if(substr($arg, 0, 2) === '--') {
                $this->parseLongOption(substr($arg, 2));
                continue;
            }

            if(substr($arg, 0, 1) === '-') {
                 $this->parseShortOption(substr($arg, 1));
                 continue;
            }

            $this->addArgument($arg);
        }

        $this->addDefaults();

        return shape(
            'input' => $this->input,
            'unnamed arguments' => $this->unnamedArguments,
        );
    }

    private function parseLongOption(string $optionText) : void
    {
        list($name, $value) = $this->splitNameAndValue($optionText);

        $definition = $this->optionDefinitions
            ->filter($d ==> $d['name'] === $name)
            ->get(0);

        if($definition === null) {
             return;
        }

        if($definition['value required'] && $value === null) {
            $value = $this->requireValue(
                $name,
                Shapes::idx($definition, 'default'),
            );
        }

        $value = $value === null ? '' : $value;

        $this->addInput($name, trim($value, '"'));
    }

    private function parseShortOption(string $optionText) : void
    {
        list($name, $value) = $this->splitNameAndValue($optionText);

        if(strlen($name) > 1) {
            $this->addAlias(substr($name, 0, 1), null, false);
            $this->parseShortOption(substr($optionText, 1));
            return;
        }

        $this->addAlias($name, $value, true);
    }

    private function addAlias(string $alias, ?string $value, bool $lookahead) : void
    {
        $definition = $this->optionDefinitions
            ->filter($d ==> Shapes::idx($d, 'alias') === $alias)
            ->get(0);

        if($definition === null) {
             return;
        }

        $name = $definition['name'];

        if($definition['value required'] && $value === null) {
            if(!$lookahead) {
                throw new Exception\MissingValue($name);
            }
            $value = $this->requireValue(
                $name,
                Shapes::idx($definition, 'default'),
            );
        }

        $value = $value === null ? '' : $value;

        $this->addInput($name, trim($value, '"'));
    }

    private function addArgument(string $value) : void
    {
        $definition = $this->argumentDefinitions->get($this->argsFound);
        $this->argsFound++;

        if($definition === null) {
            $this->unnamedArguments->add($value);
            return;
        }

        $this->addInput($definition['name'], $value);
    }

    private function peek() : ?string
    {
        if(count($this->stack)) {
            return $this->stack[0];
        }
        return null;
    }

    private function shift() : ?string
    {
        if(count($this->stack)) {
            return array_shift($this->stack);
        }
        return null;
    }

    private function addInput(string $name, string $value) : void
    {
        $values = $this->input->get($name);
        if($values === null) {
            $values = Vector{$value};
            $this->input->set($name, $values);
            return;
        }
        $values->add($value);
    }

    private function splitNameAndValue(string $nameAndValue) : (string, ?string)
    {
        $parts = explode('=', $nameAndValue, 2);
        $name = array_shift($parts);
        $value = $parts ?
            array_shift($parts) :
            null;
        return tuple($name, $value);
    }

    private function requireValue(string $name, ?string $default) : string
    {
        $next = $this->peek();
        if($next !== null && substr($next, 0, 1) !== '-') {
            $this->shift();
            return $next;
        }

        if($default !== null) {
            return $default;
        }

        throw new Exception\MissingValue($name);
    }

    private function addDefaults() : void
    {
        $addDefaultIfMissing = $d ==> {
            $default = Shapes::idx($d, 'default');
            if(
                $default !== null &&
                !$this->input->containsKey($d['name'])
            ) {
                $this->addInput($d['name'], $default);
            }
        };
        $this->argumentDefinitions->map($addDefaultIfMissing);
        $this->optionDefinitions->map($addDefaultIfMissing);
    }
}
