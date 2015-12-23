<?hh // strict

namespace HackPack\HackMini\Cli;

type Option = shape(
    'name' => string,
    'description' => ?string,
    'aliases' => ?array<string>,
    'default' => ?string,
);

type Flag = shape(
    'name' => string,
    'description' => ?string,
    'aliases' => ?array<string>,
);

type Argument = shape(
    'name' => string,
    'description' => ?string,
);

class Request
{
    private Map<string, string> $aliases = Map{};

    private Map<string, Vector<?string>> $options = Map{};
    private Map<string, int> $flags = Map{};

    private Vector<string> $argvals = Vector{};
    private Vector<string> $argnames;

    public function __construct(
        private string $name,
        \ConstVector<string> $inputArgs,
        private \ConstVector<Argument> $argDefinitions,
        private \ConstVector<Option> $optionDefinitions,
        private \ConstVector<Flag> $flagDefinitions,
    )
    {
        $this->argnames = new Vector($argDefinitions->map($a ==> $a['name']));
        $this->registerNames(
            $optionDefinitions->map($o ==> $o['name']),
            $optionDefinitions->map($o ==> $o['aliases']),
            $this->options,
            () ==> Vector{},
        );
        $this->registerNames(
            $flagDefinitions->map($o ==> $o['name']),
            $flagDefinitions->map($o ==> $o['aliases']),
            $this->flags,
            () ==> 0
        );

        $i = 0;
        while($i < $inputArgs->count()) {
            if(substr($inputArgs->at($i), 0, 2) === '--') {
                $i = $this->extractLongOption($i, $inputArgs);
                continue;
            }
            if(substr($inputArgs->at($i), 0, 1) === '-') {
                $i = $this->extractShortOption($i, $inputArgs);
                continue;
            }
            $this->argvals->add($inputArgs->at($i));
            $i++;
        }
    }

    public function option(string $name) : \ConstVector<?string>
    {
        $vals = $this->options->get($name);
        if($vals === null) {
            throw new \InvalidArgumentException('Option ' . $name . ' not defined for command ' . $this->name);
        }
        return $vals;
    }

    public function options() : \ConstMap<string, \ConstVector<?string>>
    {
         return $this->options;
    }

    public function flag(string $name) : int
    {
        $count = $this->flags->get($name);
        if($count === null) {
            throw new \InvalidArgumentException('Flag ' . $name . ' not defined for command ' . $this->name);
        }
        return $count;
    }

    public function flags() : \ConstMap<string, int>
    {
        return $this->flags;
    }

    public function arg(string $name) : ?string
    {
        $index = $this->argnames->linearSearch($name);
        if($this->argvals->containsKey($index)) {
            return $this->argvals->at($index);
        }
        throw new \InvalidArgumentException('Argument name ' . $name . ' not defined for command ' . $this->name);
    }

    public function args() : \ConstVector<string>
    {
         return $this->argvals;
    }

    private function extractLongOption(int $i, \ConstVector<string> $args) : int
    {
        $def = $this->optionDefinitions->filter($d ==> $d['name'] === $args->at($i));
        list($name, $value) = explode('=', $args->at($i), 2);
        $name = ltrim($name, '-');
        $realName = $this->aliases->get($name);

        if($realName === null) {
            return $i + 1;
        }

        if($this->flags->containsKey($realName)) {
            $this->addFlag($name);
            return $i + 1;
        }

        if( ! $this->options->containsKey($realName)) {
            return $i + 1;
        }

        $ret = $i + 1;
        if(
            $value === false &&
            $args->containsKey($i + 1) &&
            substr($args->at($i + 1), 0, 1) !== '-'
        ) {
            $value = $args->at($i + 1);
            $ret = $i + 2;
        }

        $this->addOption($realName, $value);
        return $ret;
    }

    private function extractShortOption(int $i, \ConstVector<string> $args) : int
    {
        $thisarg = $args->at($i);
        for($j = 1; $j < strlen($thisarg); $j++) {
            $name = substr($thisarg, $j, 1);
            $realName = $this->aliases->get($name);

            if($realName === null) {
                continue;
            }

            if($this->flags->containsKey($realName)) {
                $this->addFlag($realName);
                continue;
            }

            if( ! $this->options->containsKey($realName)) {
                continue;
            }

            $value = substr($thisarg, $j + 1);

            if(is_string($value)) {
                $this->addOption($realName, $value);
            }

            if(
                $value === false &&
                $args->containsKey($i + 1) &&
                substr($args->at($i + 1), 0, 1) !== '-'
            ) {
                $value = $args->at($i + 1);
                $this->addOption($realName, $value);
                return $i + 2;
            }

            $this->addOption($realName, null);
            return $i + 1;
        }
        return $i + 1;
    }

    private function addOption(string $name, ?string $value) : void
    {
        $values = $this->options->get($name);
        if($values === null) {
            $this->options->set($name, Vector{$value});
            return;
        }
        $values->add($value);
    }

    private function addFlag(string $name) : void
    {
        $count = $this->flags->get($name);
        if($count === null) {
            $count = 0;
        }
        $this->flags->set($name, $count + 1);
    }

    private function registerNames<Tval>(
        \ConstVector<string> $names,
        \ConstVector<?array<string>> $aliases,
        Map<string, Tval> $registry,
        (function():Tval) $default,
    ) : void
    {
        foreach($names as $i => $name) {
            $this->checkNames([$name]);
            $this->registerAliases($name, $aliases->get($i));
            $registry->set($name, $default());
        }
    }

    private function registerAliases(string $name, ?array<string> $aliases) : void
    {
        $this->aliases->set($name, $name);
        if($aliases !== null) {
            $this->checkNames($aliases);
            foreach($aliases as $a) {
                $this->aliases->set($a, $name);
            }
        }
    }

    private function checkNames(array<string> $names) : void
    {
        foreach($names as $name){
            if(preg_match('~=| ~', $name)) {
                throw new \InvalidArgumentException(
                    'Command line argument/option/names may not contain "=" or " " (space) because they are used as value delimiters.  Name given: ' . $name
                );
            }
        }
    }
}
