<?hh // strict

namespace HackPack\HackMini\Test\Middleware;

use FredEmmott\DefinitionFinder\FileParser;
use HackPack\HackMini\Middleware\DefinitionParser;
use HackPack\HackUnit\Contract\Assert;

class DefinitionParserTest {
  <<Test>>
  public function parseCorrect(Assert $assert): void {
    $code = <<<'Hack'
<?hh
class MiddlewareProvider
{
    <<ProvideMiddleware('class')>>
    public static function factory(FactoryContainer $c) : void { }
}

<<ProvideMiddleware('function')>>
function factory(FactoryContainer $c) : void { }
Hack;

    $parser = $this->parse($code);

    $assert->int($parser->failures()->count())->eq(0);

    $middleware = $parser->middleware();
    $assert->int($middleware->count())->eq(2);

    $assert->bool($middleware->containsKey('class'))->is(true);
    $assert->string($middleware->at('class'))
      ->is('class_meth(\'MiddlewareProvider\', \'factory\')');

    $assert->bool($middleware->containsKey('function'))->is(true);
    $assert->string($middleware->at('function'))->is('fun(\'factory\')');
  }

  <<Test>>
  public function badNameType(Assert $assert): void {
    $code = <<<'Hack'
<?hh
class MiddlewareProvider
{
    <<ProvideMiddleware(1)>>
    public static function factory(FactoryContainer $c) : void { }
}

<<ProvideMiddleware(2)>>
function factory(FactoryContainer $c) : void { }
Hack;

    $parser = $this->parse($code);
    $assert->int($parser->failures()->count())->eq(2);
    $assert->int($parser->middleware()->count())->eq(0);
  }

  <<Test>>
  public function nonStaticMethod(Assert $assert): void {
    $code = <<<'Hack'
<?hh
class MiddlewareProvider
{
    <<ProvideMiddleware('class')>>
    public function factory(FactoryContainer $c) : void { }
}
Hack;

    $parser = $this->parse($code);
    $assert->int($parser->failures()->count())->eq(1);
    $assert->int($parser->middleware()->count())->eq(0);
  }

  <<Test>>
  public function tooFewParams(Assert $assert): void {
    $code = <<<'Hack'
<?hh
class MiddlewareProvider
{
    <<ProvideMiddleware('class')>>
    public static function factory() : void { }
}

<<ProvideMiddleware('function')>>
function factory() : void { }
Hack;

    $parser = $this->parse($code);
    $assert->int($parser->failures()->count())->eq(2);
    $assert->int($parser->middleware()->count())->eq(0);
  }

  <<Test>>
  public function tooManyParams(Assert $assert): void {
    $code = <<<'Hack'
<?hh
class MiddlewareProvider
{
    <<ProvideMiddleware('class')>>
    public static function factory(FactoryContainer $c, int $other) : void { }
}

<<ProvideMiddleware('function')>>
function factory(FactoryContainer $c, int $other) : void { }
Hack;

    $parser = $this->parse($code);
    $assert->int($parser->failures()->count())->eq(2);
    $assert->int($parser->middleware()->count())->eq(0);
  }

  <<Test>>
  public function enoughRequiredParams(Assert $assert): void {
    $code = <<<'Hack'
<?hh
class MiddlewareProvider
{
    <<ProvideMiddleware('class')>>
    public static function factory(FactoryContainer $c, ?string $other = null) : void
    { }
}

<<ProvideMiddleware('function')>>
function factory(FactoryContainer $c, ?string $other = null) : void { }
Hack;

    $parser = $this->parse($code);
    $assert->int($parser->failures()->count())->eq(0);
    $assert->int($parser->middleware()->count())->eq(2);
  }

  <<Test>>
  public function repeatedMiddlewareName(Assert $assert): void {
    $code = <<<'Hack'
<?hh
class MiddlewareProvider
{
    <<ProvideMiddleware('name')>>
    public static function factory(FactoryContainer $c, int $other = 0) : void
    { }
}

<<ProvideMiddleware('name')>>
function factory(FactoryContainer $c, int $other = 0) : void { }
Hack;

    $parser = $this->parse($code);
    $assert->int($parser->failures()->count())->eq(1);
    $assert->int($parser->middleware()->count())->eq(1);
  }

  private function parse(string $code): DefinitionParser {
    $fileParser = FileParser::FromData($code);
    $functions = $fileParser->getFunctions();
    $classes = $fileParser->getClasses();
    return new DefinitionParser($functions, $classes);
  }

}
