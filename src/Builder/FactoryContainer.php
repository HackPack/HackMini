<?hh // strict

namespace HackPack\HackMini\Builder;

use FredEmmott\DefinitionFinder\ScannedBasicClass;
use FredEmmott\DefinitionFinder\ScannedFunction;
use FredEmmott\DefinitionFinder\ScannedFunctionAbstract;

newtype MethodFactoryParts = shape(
    'method' => string,
    'class' => string,
    'return' => string,
    'name' => string,
);

newtype FunctionFactoryParts = shape(
    'function' => string,
    'return' => string,
    'name' => string,
);

class FactoryContainer
{
    /*
     * Start head string
     */
    private static string $head = <<<'PHP'
<?hh // strict

/*
 * This file is generated, do not modify it directly
 *
 * To include your own factories in this file, annotate them with <<provides('YourNameHere')>>
 */

namespace HackPack\HackMini;

use HackPack\HackMini\Exception\CircularDependency;

class FactoryContainer
{
    private Vector<string> $loading = Set{};
PHP;
    /*
     * End head string
     */

    /*
     * Start foot string
     */
    private static string $foot = <<<'PHP'

    private function load<T>(string $name, (function(FactoryContainer):T) $factory) : T
    {
        if($this->loading->linearSearch($name) !== -1) {
            $list = $this->loading->toVector();
            $this->loading->clear();
            throw new CircularDependency($name, $list);
        }

        $this->loading->add($name);
        $result = $factory($this);
        $this->loading->pop();

        return $result;
    }
}
PHP;
    /*
     * End foot string
     */

    private string $body = '';

    public function contents() : string
    {
        return self::$head . $this->body . self::$foot;
    }

    public function addClasses(\ConstVector<ScannedBasicClass> $classes) : void
    {
        foreach($classes as $class) {
            foreach($class->getMethods() as $method) {
                $parts = $this->getMethodFactoryParts($class, $method);
                if($parts !== null) {
                    $this->addMethodFactory($parts);
                }
            }
        }
    }

    public function addFunctions(\ConstVector<ScannedFunction> $functions) : void
    {
        foreach($functions as $function) {
            $parts = $this->getFunctionFactoryParts($function);
            if($parts !== null) {
                $this->addFunctionFactory($parts);
            }
        }
    }

    private function getMethodFactoryParts(ScannedBasicClass $class, ScannedMethod $method) : ?MethodFactoryParts
    {
        $types = $method->getParameters();
        if($types->count() !== 1) {
            return null;
        }
        $hint = $types->at(0)->getTyphint();

    }

    private function addMethodFactory(MethodFactoryParts $parts) : void
    {
        $name = ucfirst($name);

        /*
         * Start factory alias string
         */
        $this->body .= <<<PHP

    <<__Memoize>>
    public function get{$name}() : {$return}
    {
        return this->new{$name}();
    }

    public function new{$name}() : {$return}
    {
         return $this->load('{$name}', class_meth('{$class}', '{$method}'));
    }
PHP;
        /*
         * End factory alias string
         */
    }

    private function addFunctionFactory(FunctionFactoryParts $parts) : void
    {
        $name = ucfirst($name);

        /*
         * Start factory alias string
         */
        $this->body .= <<<PHP

    <<__Memoize>>
    public function get{$name}() : {$return}
    {
        return this->new{$name}();
    }

    public function new{$name}() : {$return}
    {
         return $this->load('{$name}', fun('{$function}'));
    }
PHP;
        /*
         * End factory alias string
         */
    }

}
