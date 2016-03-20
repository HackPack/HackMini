<?hh // strict

namespace HackPack\HackMini\Test\Container;

use FredEmmott\DefinitionFinder\FileParser;
use HackPack\HackMini\Container\DefinitionParser;
use HackPack\HackUnit\Contract\Assert;

class DefinitionParserTest
{
    <<Test>>
    public function parseFunction(Assert $assert) : void
    {
        $code = <<<'Hack'
<?hh

<<Provides('name')>>
function factory(FactoryContainer $c) : int
{
    return 1;
}
Hack;
        $parser = $this->parse($code);

        $assert->int($parser->failures()->count())->eq(0);
        $assert->int($parser->services()->count())->eq(1);

        $service = $parser->services()->at(0);

        $assert->string($service['name'])->is('name');
        $assert->string($service['return'])->is('int');
        $assert->string(
            Shapes::idx($service, 'function', '')
        )->is('factory');
        $assert->mixed(
            Shapes::idx($service, 'class')
        )->isNull();
        $assert->mixed(
            Shapes::idx($service, 'method')
        )->isNull();
    }

    <<Test>>
    public function parseMethod(Assert $assert) : void
    {
        $code = <<<'Hack'
<?hh

class Service
{
    <<Provides('name')>>
    public static function factory(FactoryContainer $c) : this
    {
    }
}
Hack;
        $parser = $this->parse($code);

        $assert->int($parser->failures()->count())->eq(0);
        $assert->int($parser->services()->count())->eq(1);

        $service = $parser->services()->at(0);

        $assert->string($service['name'])->is('name');
        $assert->string($service['return'])->is('Service');
        $assert->mixed(
            Shapes::idx($service, 'function')
        )->isNull();
        $assert->string(
            Shapes::idx($service, 'class', '')
        )->is('Service');
        $assert->string(
            Shapes::idx($service, 'method', '')
        )->is('factory');
    }

    <<Test>>
    public function repeatedName(Assert $assert) : void
    {
        $code = <<<'Hack'
<?hh

<<Provides('service')>>
function factory(FactoryContainer $c) : void { }

class Service
{
    <<Provides('service')>>
    public static function factory(FactoryContainer $c) : void { }
}
Hack;

        $parser = $this->parse($code);

        $assert->int($parser->failures()->count())->eq(1);
        $assert->int($parser->services()->count())->eq(1);
    }

    <<Test>>
    public function instanceMethod(Assert $assert) : void
    {
        $code = <<<'Hack'
<?hh

class Service
{
    <<Provides('service')>>
    public function factory(FactoryContainer $c) : void { }
}
Hack;

        $parser = $this->parse($code);

        $assert->int($parser->failures()->count())->eq(1);
        $assert->int($parser->services()->count())->eq(0);
    }

    <<Test>>
    public function onlyAnnotatedFunctions(Assert $assert) : void
    {
        $code = <<<'Hack'
<?hh

<<Provides('service1')>>
function factory(FactoryContainer $c) : void { }

function factory2(FactoryContainer $c) : void { }

class Service
{
    <<Provides('service2')>>
    public static function factory(FactoryContainer $c) : void { }

    public static function factory2(FactoryContainer $c) : void { }
}
Hack;

        $parser = $this->parse($code);

        $assert->int($parser->failures()->count())->eq(0);
        $assert->int($parser->services()->count())->eq(2);
    }

    <<Test>>
    public function tooManyParams(Assert $assert) : void
    {
        $code = <<<'Hack'
<?hh

<<Provides('service1')>>
function factory(FactoryContainer $c, int $int) : void { }
Hack;

        $parser = $this->parse($code);

        $assert->int($parser->failures()->count())->eq(1);
        $assert->int($parser->services()->count())->eq(0);
    }

    <<Test>>
    public function tooFewParams(Assert $assert) : void
    {
        $code = <<<'Hack'
<?hh

<<Provides('service1')>>
function factory() : void { }
Hack;

        $parser = $this->parse($code);

        $assert->int($parser->failures()->count())->eq(1);
        $assert->int($parser->services()->count())->eq(0);
    }

    <<Test>>
    public function enoughRequiredParams(Assert $assert) : void
    {
        $code = <<<'Hack'
<?hh

<<Provides('service1')>>
function factory(FactoryContainer $c, int $int = 0) : void { }
Hack;

        $parser = $this->parse($code);

        $assert->int($parser->failures()->count())->eq(0);
        $assert->int($parser->services()->count())->eq(1);
    }

    private function parse(string $code) : DefinitionParser
    {
        $fileParser = FileParser::FromData($code);
        $functions = $fileParser->getFunctions();
        $classes = $fileParser->getClasses();
        return new DefinitionParser($functions, $classes);
    }
}
