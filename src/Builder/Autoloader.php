<?hh // strict

namespace HackPack\HackMini\Builder;

class Autoloader
{

    private static string $head = <<<'PHP'
<?hh // strict

namespace HackPack\HackMini;

function register_autoloader() : void
{
    \HH\autoload_set_paths(
PHP;

    private static string $foot = <<<'PHP'
    )
}
PHP;

    private Map<string,string> $classes = Map{};
    private Map<string,string> $functions = Map{};
    private Map<string,string> $types = Map{};
    private Map<string,string> $constants = Map{};

    public function addClass(string $name, string $path) : this
    {
        $this->classes->set($name, $path);
        return $this;
    }

    public function addFunction(string $name, string $path) : this
    {
        $this->functions->set($name, $path);
        return $this;
    }

    public function addConstant(string $name, string $path) : this
    {
        $this->functions->set($name, $path);
        return $this;
    }

    public function addType(string $name, string $path) : this
    {
        $this->types->set($name, $path);
        return $this;
    }

    public function content() : string
    {
        $fullMap = [
            'class' => $this->classes->toArray(),
            'function' => $this->functions->toArray(),
            'type' => $this->types->toArray(),
            'constant' => $this->constants->toArray(),
        ];
        return implode('', [
            self::$head,
            var_export($fullMap, true),
            self::$foot,
        ]);
    }
}
