<?hh // strict

namespace HackPack\HackMini\Command;

<<__ConsistentConstruct>>
class Request
{
    private \ConstMap<string, \ConstVector<string>> $input = Map{};
    private \ConstVector<string> $unnamedArguments = Vector{};
    private \ConstVector<ArgumentDefinition> $argumentDefinitions = Vector{};
    private \ConstVector<OptionDefinition> $optionDefinitions = Vector{};

    public static function fromEnv(array<string> $argv, string $projectRoot) : this
    {
        if(count($argv) < 2) {
            return new static('help', Vector{}, $projectRoot, new Parser());
        }

        $invocation = array_shift($argv);
        $commandName = array_shift($argv);
        return new static($commandName, new Vector($argv), $projectRoot, new Parser());
    }

    public function __construct(
        private string $name,
        private \ConstVector<string> $rawInput,
        private string $projectRoot,
        private Parser $parser,
    )
    {
        $this->parseInput();
    }

    public function get(string $name) : ?\ConstVector<string>
    {
        if($this->nameUndefined($name)) {
            throw new Exception\UndefinedInput($this, $name);
        }
        return $this->input->get($name);
    }

    public function getFirst(string $name) : ?string
    {
        return $this->get($name)?->at(0);
    }

    public function getLast(string $name) : ?string
    {
        $values = $this->get($name);
        if($values === null || $values->isEmpty()) {
            return null;
        }
        return $values->at($values->count() - 1);
    }

    public function at(string $name) : \ConstVector<string>
    {
        $value = $this->get($name);
        if($value === null) {
             throw new Exception\MissingInput($this, $name);
        }
        return $value;
    }

    public function atFirst(string $name) : string
    {
        return $this->at($name)->at(0);
    }

    public function atLast(string $name) : string
    {
        $values = $this->at($name);
        return $values->at($values->count() - 1);
    }

    public function unnamedArguments() : \ConstVector<string>
    {
        return $this->unnamedArguments;
    }

    public function projectRoot() : string
    {
         return $this->projectRoot;
    }

    public function withProjectRoot(string $projectRoot) : this
    {
        $new = clone $this;
        $new->projectRoot = $projectRoot;
        return $new;
    }

    public function name() : string
    {
        return $this->name;
    }

    public function withName(string $name) : this
    {
        $new = clone $this;
        $new->name = $name;
        return $new;
    }

    public function withArguments(Vector<ArgumentDefinition> $argumentDefinitions) : this
    {
        $new = clone $this;
        $new->argumentDefinitions = $argumentDefinitions;
        $new->parseInput();
        return $new;
    }

    public function withOptions(Vector<OptionDefinition> $optionDefinitions) : this
    {
        $new = clone $this;
        $new->optionDefinitions = $optionDefinitions;
        $new->parseInput();
        return $new;
    }

    public function withRawInput(\ConstVector<string> $input) : this
    {
        $new = clone $this;
        $new->rawInput = $input;
        $new->parseInput();
        return $new;
    }

    public function rawInput() : \ConstVector<string>
    {
        return $this->rawInput;
    }

    private function parseInput() : void
    {
        $results = $this->parser->parse(
            $this->rawInput,
            $this->argumentDefinitions,
            $this->optionDefinitions,
        );
        $this->input = $results['input'];
        $this->unnamedArguments = $results['unnamed arguments'];
    }


    private function nameUndefined(string $name) : bool
    {
        foreach($this->argumentDefinitions as $def) {
            if($def['name'] === $name) {
                return false;
            }
        }

        foreach($this->optionDefinitions as $def) {
            if($def['name'] === $name) {
                return false;
            }
        }

        return true;
    }
}
