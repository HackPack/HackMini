<?hh // strict

namespace HackPack\HackMini\Test\Routes;

use HackPack\HackUnit\Contract\Assert;
use HackPack\HackMini\Routes\DefinitionParser;
use HackPack\HackMini\Message\RestMethod;
use FredEmmott\DefinitionFinder\FileParser;

class DefinitionParserTest {
  <<Test>>
  public function withVerbs(Assert $assert): void {
    $code = <<<'Hack'
<?hh // strict

<<Route('delete', 'path')>>
function deleteHandler(
  \FactoryContainer $c,
  HackPack\HackMini\Message\Request $req,
  HackPack\HackMini\Message\Response $rsp,
) : HackPack\HackMini\Message\Response
{
  return $rsp;
}

<<Route('get', 'path')>>
function getHandler(
  \FactoryContainer $c,
  HackPack\HackMini\Message\Request $req,
  HackPack\HackMini\Message\Response $rsp,
) : HackPack\HackMini\Message\Response
{
  return $rsp;
}

<<Route('head', 'path')>>
function headHandler(
  \FactoryContainer $c,
  HackPack\HackMini\Message\Request $req,
  HackPack\HackMini\Message\Response $rsp,
) : HackPack\HackMini\Message\Response
{
  return $rsp;
}

<<Route('options', 'path')>>
function optionsHandler(
  \FactoryContainer $c,
  HackPack\HackMini\Message\Request $req,
  HackPack\HackMini\Message\Response $rsp,
) : HackPack\HackMini\Message\Response
{
  return $rsp;
}

<<Route('patch', 'path')>>
function patchHandler(
  \FactoryContainer $c,
  HackPack\HackMini\Message\Request $req,
  HackPack\HackMini\Message\Response $rsp,
) : HackPack\HackMini\Message\Response
{
  return $rsp;
}

<<Route('post', 'path')>>
function postHandler(
  \FactoryContainer $c,
  HackPack\HackMini\Message\Request $req,
  HackPack\HackMini\Message\Response $rsp,
) : HackPack\HackMini\Message\Response
{
  return $rsp;
}

<<Route('put', 'path')>>
function putHandler(
  \FactoryContainer $c,
  HackPack\HackMini\Message\Request $req,
  HackPack\HackMini\Message\Response $rsp,
) : HackPack\HackMini\Message\Response
{
  return $rsp;
}
Hack;

    $parser = $this->parse($code);
    $assert->int($parser->failures()->count())->eq(0);

    $routes = $parser->routes();
    $assert->int($routes->count())->eq(7);

    $assert->string($routes->at(0)['verb'])->is(RestMethod::Delete);
    $assert->string($routes->at(1)['verb'])->is(RestMethod::Get);
    $assert->string($routes->at(2)['verb'])->is(RestMethod::Head);
    $assert->string($routes->at(3)['verb'])->is(RestMethod::Options);
    $assert->string($routes->at(4)['verb'])->is(RestMethod::Patch);
    $assert->string($routes->at(5)['verb'])->is(RestMethod::Post);
    $assert->string($routes->at(6)['verb'])->is(RestMethod::Put);

  }

  <<Test>>
  public function unknownVerb(Assert $assert): void {
    $code = <<<'Hack'
<?hh
<<Route('strange', '/')>>
function handler(
  \FactoryContainer $c,
  HackPack\HackMini\Message\Request $req,
  HackPack\HackMini\Message\Response $rsp,
) : HackPack\HackMini\Message\Response
{}
Hack;

    $parser = $this->parse($code);
    $assert->int($parser->routes()->count())->eq(0);
    $assert->int($parser->failures()->count())->eq(1);
    $assert->string($parser->failures()->at(0)['reason'])
      ->contains('Unknown REST verb');
  }

  <<Test>>
  public function functionHandler(Assert $assert): void {
    $code = <<<'Hack'
<?hh
<<Route('/some/pattern')>>
function handler(
  \FactoryContainer $c,
  HackPack\HackMini\Message\Request $req,
  HackPack\HackMini\Message\Response $rsp,
) : HackPack\HackMini\Message\Response
{}
Hack;

    $parser = $this->parse($code);
    $assert->int($parser->failures()->count())->eq(0);

    $assert->int($parser->routes()->count())->eq(1);
    $route = $parser->routes()->at(0);
    $assert->mixed(Shapes::idx($route, 'function'))->identicalTo('handler');
    $assert->mixed(Shapes::idx($route, 'class'))->isNull();
    $assert->mixed(Shapes::idx($route, 'method'))->isNull();
    $assert->string($route['verb'])->is(RestMethod::Any);
    $assert->string($route['pattern'])->is('/some/pattern');
  }

  <<Test>>
  public function methodHandler(Assert $assert): void {
    $code = <<<'Hack'
<?hh
class RouteHandler
{
  <<Route('/some/pattern')>>
  public static function handler(
    \FactoryContainer $c,
    HackPack\HackMini\Message\Request $req,
    HackPack\HackMini\Message\Response $rsp,
  ) : HackPack\HackMini\Message\Response
  {}
}
Hack;

    $parser = $this->parse($code);
    $assert->int($parser->failures()->count())->eq(0);

    $assert->int($parser->routes()->count())->eq(1);
    $route = $parser->routes()->at(0);
    $assert->mixed(Shapes::idx($route, 'function'))->isNull();
    $assert->mixed(Shapes::idx($route, 'class'))->identicalTo('RouteHandler');
    $assert->mixed(Shapes::idx($route, 'method'))->identicalTo('handler');
    $assert->string($route['verb'])->is(RestMethod::Any);
    $assert->string($route['pattern'])->is('/some/pattern');
  }

  <<Test>>
  public function instanceMethod(Assert $assert): void {
    $code = <<<'Hack'
<?hh
class RouteHandler
{
  <<Route('/some/pattern')>>
  public function handler(
    \FactoryContainer $c,
    HackPack\HackMini\Message\Request $req,
    HackPack\HackMini\Message\Response $rsp,
  ) : HackPack\HackMini\Message\Response
  {}
}
Hack;

    $parser = $this->parse($code);
    $assert->int($parser->failures()->count())->eq(1);
    $assert->string($parser->failures()->at(0)['reason'])
      ->contains('Route handler methods must be static');

    $assert->int($parser->routes()->count())->eq(0);
  }

  <<Test>>
  public function middleware(Assert $assert): void {
    $code = <<<'Hack'
<?hh
<<Route('/'), UseMiddleware('some middleware', 'then some other middleware')>>
function handler(
  \FactoryContainer $c,
  HackPack\HackMini\Message\Request $req,
  HackPack\HackMini\Message\Response $rsp,
) : HackPack\HackMini\Message\Response
{}
Hack;

    $parser = $this->parse($code);
    $assert->int($parser->failures()->count())->eq(0);
    $assert->int($parser->routes()->count())->eq(1);

    $middleware = $parser->routes()->at(0)['middleware'];
    $assert->int($middleware->count())->eq(2);
    $assert->string($middleware->at(0))->is('some middleware');
    $assert->string($middleware->at(1))->is('then some other middleware');
  }

  <<Test>>
  public function namespaced(Assert $assert) : void
  {
    $code = <<<'Hack'
<?hh
namespace Some\NS;
use FactoryContainer;

<<Route('/')>>
function handler(
  FactoryContainer $c,
  HackPack\HackMini\Message\Request $req,
  HackPack\HackMini\Message\Response $rsp,
) : HackPack\HackMini\Message\Response
{}
Hack;
    $parser = $this->parse($code);
    $assert->int($parser->failures()->count())->eq(0);
    $assert->int($parser->routes()->count())->eq(1);
  }
  private function parse(string $code): DefinitionParser {
    $hackParser = FileParser::FromData($code);
    return new DefinitionParser(
      $hackParser->getFunctions(),
      $hackParser->getClasses(),
    );
  }
}
