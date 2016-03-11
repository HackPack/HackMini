<?hh // strict

namespace HackPack\HackMini\Command;

class Request
{
    private Map<string, Vector<string>> $input = Map{};
    private Vector<string> $argumentDefinitions = Vector{};
    private Vector<OptionDefinition> $optionDefinitions = Vector{};

    public function __construct(
        private string $command,
        private Vector<string> $rawInput,
        private string $projectRoot,
    )
    {
        $this->parseInput();
    }

    public function get(string $name) : ?Vector<string>
    {
        return $this->input->get($name);
    }

    public function getFirst(string $name) : ?string
    {
        return $this->get($name)?->get(0);
    }

    public function getLast(string $name) : ?string
    {
        $values = $this->get($name);
        if($values === null || $values->isEmpty()) {
            return null;
        }
        return $values->at($values->count() - 1);
    }

    public function at(string $name) : Vector<string>
    {
        return $this->input->at($name);
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

    public function projectRoot() : string
    {
         return $this->projectRoot;
    }

    public function commandText() : string
    {
        return $this->command;
    }

    public function withArguments(Vector<string> $argumentDefinitions) : this
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

    private function parseInput() : void
    {
    }
}
