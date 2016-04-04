<?hh // strict

namespace HackPack\HackMini\Test\Routes;

use HackPack\HackUnit\Contract\Assert;
use HackPack\HackMini\Routes\Builder;
use HackPack\HackMini\Message\RestMethod;

class BuilderTest {

  <<Test>>
  public function includeHeaderAndFooter(Assert $assert): void {
    $code = (new Builder(Vector {}, Map {}))->render();

    $assert->string($code)->contains(Builder::HEAD.PHP_EOL);

    $headRemoved = strstr($code, Builder::HEAD);
    $assert->string($headRemoved)->contains(PHP_EOL.Builder::FOOT);
  }

  <<Test>>
  public function middlewareStack(Assert $assert): void {
    $code = (new Builder(
      Vector {
        shape(
          'function' => 'handler',
          'middleware' => Vector {'one', 'two'},
          'pattern' => '/one',
          'verb' => RestMethod::Get,
        ),
        shape(
          'function' => 'handlerTwo',
          'middleware' => Vector {'two'},
          'pattern' => '/two',
          'verb' => RestMethod::Get,
        ),
      },
      Map {'one' => 'oneMiddleware', 'two' => 'twoMiddleware'},
    ))->render();

    $one = <<<'Hack'
        'middleware' => Vector {
          oneMiddleware,
          twoMiddleware,
        },
Hack;

    $two = <<<'Hack'
        'middleware' => Vector {
          twoMiddleware,
        },
Hack;

    $assert->string($code)->contains($one);
    $assert->string($code)->contains($two);
  }

  <<Test>>
  public function functionHandler(Assert $assert): void {
    $code = (new Builder(
      Vector {
        shape(
          'function' => 'handler',
          'middleware' => Vector {},
          'pattern' => '/pattern',
          'verb' => RestMethod::Get,
        ),
      },
      Map {},
    ))->render();

    $expected = <<<'Hack'
      '/pattern' => shape(
        'handler' => fun('handler'),
        'middleware' => Vector {},
      ),
Hack;

    $assert->string($code)->contains(PHP_EOL.$expected.PHP_EOL);
  }

  <<Test>>
  public function methodHandler(Assert $assert): void {
    $code = (new Builder(
      Vector {
        shape(
          'class' => 'HandlerClass',
          'method' => 'handler',
          'middleware' => Vector {},
          'pattern' => '/pattern',
          'verb' => RestMethod::Get,
        ),
      },
      Map {},
    ))->render();

    $expected = <<<'Hack'
      '/pattern' => shape(
        'handler' => class_meth('HandlerClass', 'handler'),
        'middleware' => Vector {},
      ),
Hack;

    $assert->string($code)->contains(PHP_EOL.$expected.PHP_EOL);
  }

  <<Test>>
  public function noHandler(Assert $assert): void {
    $builder = (new Builder(
      Vector {
        shape(
          'middleware' => Vector {},
          'pattern' => '/pattern',
          'verb' => RestMethod::Get,
        ),
      },
      Map {},
    ));
    $assert->whenCalled(
      () ==> {
        $builder->render();
      },
    )->willThrowClass(\UnexpectedValueException::class);
  }

  <<Test>>
  public function noMiddleware(Assert $assert): void {
    $builder = new Builder(
      Vector {
        shape(
          'middleware' => Vector {'dne'},
          'pattern' => '/',
          'verb' => RestMethod::Get,
          'function' => 'handler',
        ),
      },
      Map {},
    );

    $assert->whenCalled(
      () ==> {
        $builder->render();
      },
    )->willThrowClass(
      \HackPack\HackMini\Middleware\UndefinedMiddleware::class,
    );
  }

  <<Test>>
  public function restMethods(Assert $assert): void {
    $code = (new Builder(
      Vector {
        shape(
          'middleware' => Vector {},
          'pattern' => '/',
          'verb' => RestMethod::Post,
          'function' => 'handler',
        ),
        shape(
          'middleware' => Vector {},
          'pattern' => '/',
          'verb' => RestMethod::Get,
          'function' => 'handler',
        ),
      },
      Map {},
    ))->render();

    $get = <<<'Hack'
    RestMethod::Get => Map {
Hack;

    $post = <<<'Hack'
    RestMethod::Post => Map {
Hack;

    $put = <<<'Hack'
    RestMethod::Put => Map {
Hack;

    $assert->string($code)->contains($get);
    $assert->string($code)->contains($post);
    $assert->string($code)->not()->contains($put);
  }
}
