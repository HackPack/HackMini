<?hh // strict

namespace HackPack\HackMini\Test\Container;

use HackPack\HackMini\Container\Builder;
use HackPack\HackUnit\Contract\Assert;

class BuilderTest {
  <<Test>>
  public function includeHeaderAndFooter(Assert $assert): void {
    $code = (new Builder(Vector {}))->render();

    $assert->string($code)->contains(Builder::HEAD.PHP_EOL);

    $headRemoved = strstr($code, Builder::HEAD);
    $assert->string($headRemoved)->contains(PHP_EOL.Builder::FOOT);
  }

  <<Test>>
  public function renderGetAndNew(Assert $assert): void {
    $code =
      (new Builder(
        Vector {
          shape(
            'name' => 'myService',
            'return' => 'MyReturn',
            'function' => 'function_name',
          ),
        },
      ))->render();

    $get = <<<'Hack'

    <<__Memoize>>
    public function getMyService() : MyReturn
    {
        return $this->newMyService();
    }

Hack;
    $new = <<<'Hack'

    public function newMyService() : MyReturn
    {
Hack;
    $assert->string($code)->contains($get);
    $assert->string($code)->contains($new);
  }

  <<Test>>
  public function renderFunction(Assert $assert): void {
    $code =
      (new Builder(
        Vector {
          shape(
            'name' => 'myService',
            'return' => 'MyReturn',
            'function' => 'function_name',
          ),
        },
      ))->render();

    $expected = <<<'Hack'
        return $this->__build__('MyService', fun('function_name'));
Hack;
    $assert->string($code)->contains($expected);
  }

  <<Test>>
  public function renderMethod(Assert $assert): void {
    $code =
      (new Builder(
        Vector {
          shape(
            'name' => 'myService',
            'return' => 'MyReturn',
            'class' => 'MyClass',
            'method' => 'myMethod',
          ),
        },
      ))->render();

    $expected = <<<'Hack'
        return $this->__build__('MyService', class_meth('MyClass', 'myMethod'));
Hack;
    $assert->string($code)->contains($expected);
  }
}
