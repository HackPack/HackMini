<?hh // strict

namespace HackPack\HackMini\Test\Command;

use HackPack\HackMini\Command\Parser;
use HackPack\HackMini\Command\Request;
use HackPack\HackUnit\Contract\Assert;

class RequestTest {
  private Parser $parser;

  public function __construct() {
    $this->parser = new Parser();
  }
  <<Test>>
  public function missingCommandNameBuildsHelp(Assert $assert): void {
    $req = Request::fromEnv([], '');
    $assert->string($req->name())->is('help');

    $req = Request::fromEnv(['invocation'], '');
    $assert->string($req->name())->is('help');
  }

  <<Test>>
  public function passesCorrectNameAndArguments(Assert $assert): void {
    $req = Request::fromEnv(['invocation', 'name', 'raw1', '--raw2'], '');
    $assert->string($req->name())->is('name');

    $rawArgs = $req->rawInput();
    $assert->int($rawArgs->count())->eq(2);
    $assert->string($rawArgs->at(0))->is('raw1');
    $assert->string($rawArgs->at(1))->is('--raw2');
  }

  <<Test>>
  public function undefinedInput(Assert $assert): void {
    $req = new Request('', Vector {}, '', $this->parser);

    $assert->whenCalled(
      () ==> {
        $req->get('a');
      },
    )->willThrowClass(
      \HackPack\HackMini\Command\Exception\UndefinedInput::class,
    );

    $assert->whenCalled(
      () ==> {
        $req->at('a');
      },
    )->willThrowClass(
      \HackPack\HackMini\Command\Exception\UndefinedInput::class,
    );
  }

  <<Test>>
  public function missingArgument(Assert $assert): void {
    $req = new Request('', Vector {}, '', $this->parser);
    $req = $req->withArguments(
      Vector {shape('name' => 'arg1', 'description' => '')},
    );

    $assert->whenCalled(
      () ==> {
        $req->get('arg1');
      },
    )->willNotThrow();

    $assert->mixed($req->get('arg1'))->isNull();

    $assert->whenCalled(
      () ==> {
        $req->at('arg1');
      },
    )->willThrowClass(
      \HackPack\HackMini\Command\Exception\MissingInput::class,
    );
  }

  <<Test>>
  public function missingOption(Assert $assert): void {
    $req = new Request('', Vector {}, '', $this->parser);
    $req = $req->withOptions(
      Vector {
        shape(
          'name' => 'arg1',
          'value required' => false,
          'description' => '',
        ),
      },
    );

    $assert->whenCalled(
      () ==> {
        $req->get('arg1');
      },
    )->willNotThrow();

    $assert->mixed($req->get('arg1'))->isNull();

    $assert->whenCalled(
      () ==> {
        $req->at('arg1');
      },
    )->willThrowClass(
      \HackPack\HackMini\Command\Exception\MissingInput::class,
    );
  }

  <<Test>>
  public function singleArgument(Assert $assert): void {
    $req =
      (new Request('', Vector {'value'}, '', $this->parser))
        ->withArguments(Vector {shape('name' => 'arg', 'description' => '')});

    $assert->whenCalled(
      () ==> {
        $req->at('arg');
      },
    )->willNotThrow();

    $assert->int($req->at('arg')->count())->eq(1);
    $assert->string($req->atFirst('arg'))->is('value');
  }

  <<Test>>
  public function argumentDefault(Assert $assert): void {
    $req =
      (new Request('', Vector {}, '', $this->parser))->withArguments(
        Vector {
          shape('name' => 'arg', 'default' => 'value', 'description' => ''),
        },
      );

    $assert->whenCalled(
      () ==> {
        $req->at('arg');
      },
    )->willNotThrow();

    $assert->int($req->at('arg')->count())->eq(1);
    $assert->string($req->atFirst('arg'))->is('value');
  }

  <<Test>>
  public function twoArguments(Assert $assert): void {
    $req =
      (new Request('', Vector {'val1', 'val 2'}, '', $this->parser))
        ->withArguments(
          Vector {
            shape('name' => 'arg1', 'description' => ''),
            shape('name' => 'arg2', 'description' => ''),
          },
        );

    $assert->whenCalled(
      () ==> {
        $req->at('arg1');
        $req->at('arg2');
      },
    )->willNotThrow();

    $assert->string($req->atFirst('arg1'))->is('val1');
    $assert->string($req->atFirst('arg2'))->is('val 2');
  }

  <<Test>>
  public function argOptArg(Assert $assert): void {
    $req =
      (new Request('', Vector {'val1', '--opt', 'val 2'}, '', $this->parser))
        ->withArguments(
          Vector {
            shape('name' => 'arg1', 'description' => ''),
            shape('name' => 'arg2', 'description' => ''),
          },
        )
        ->withOptions(
          Vector {
            shape(
              'name' => 'opt',
              'value required' => false,
              'description' => '',
            ),
          },
        );

    $assert->whenCalled(
      () ==> {
        $req->at('arg1');
        $req->at('arg2');
        $req->at('opt');
      },
    )->willNotThrow();

    $assert->string($req->atFirst('arg1'))->is('val1');
    $assert->string($req->atFirst('arg2'))->is('val 2');
    $assert->string($req->atFirst('opt'))->is('');
  }

  <<Test>>
  public function argOptValueArgMissing(Assert $assert): void {
    $req =
      (new Request('', Vector {'val1', '--opt', 'val 2'}, '', $this->parser))
        ->withArguments(
          Vector {
            shape('name' => 'arg1', 'description' => ''),
            shape('name' => 'arg2', 'description' => ''),
          },
        )
        ->withOptions(
          Vector {
            shape(
              'name' => 'opt',
              'value required' => true,
              'description' => '',
            ),
          },
        );

    $assert->whenCalled(
      () ==> {
        $req->at('arg1');
        $req->at('opt');
      },
    )->willNotThrow();

    $assert->whenCalled(
      () ==> {
        $req->at('arg2');
      },
    )->willThrowClass(
      \HackPack\HackMini\Command\Exception\MissingInput::class,
    );

    $assert->string($req->atFirst('arg1'))->is('val1');
    $assert->mixed($req->getFirst('arg2'))->isNull();
    $assert->string($req->atFirst('opt'))->is('val 2');
  }

  <<Test>>
  public function optionWithoutValue(Assert $assert): void {
    $req =
      (new Request(
        '',
        Vector {
          '-o',
          'some value',
          '-oo',
          '-othero=stuff',
          '--one="quoted stuff"',
          '--one',
          '--one',
          'this value is here',
          'this value is not',
          '-one="value associated with the e"',
        },
        '',
        $this->parser,
      ))->withOptions(
        Vector {
          shape(
            'name' => 'one',
            'alias' => 'o',
            'value required' => false,
            'description' => '',
          ),
        },
      );

    $expectedValues = Vector {
      '',
      '',
      '',
      '',
      'stuff',
      'quoted stuff',
      '',
      '',
      '',
    };

    $assert->whenCalled(
      () ==> {
        $req->at('one');
      },
    )->willNotThrow();

    $values = $req->at('one');
    $assert->int($values->count())->eq($expectedValues->count());
    foreach ($values as $index => $value) {
      $assert->string($value)->is($expectedValues->at($index));
    }
  }

  <<Test>>
  public function optionWithValue(Assert $assert): void {
    $req =
      (new Request(
        '',
        Vector {
          '-o',
          'some value',
          'not this value',
          '-o=stuff',
          'not this one',
          '--one="quoted stuff"',
          '--one',
          'this value is here',
          'this value is not',
          '-stuffo',
          'last value',
        },
        '',
        $this->parser,
      ))->withOptions(
        Vector {
          shape(
            'name' => 'one',
            'alias' => 'o',
            'value required' => true,
            'description' => '',
          ),
        },
      );

    $expectedValues = Vector {
      'some value',
      'stuff',
      'quoted stuff',
      'this value is here',
      'last value',
    };

    $assert->whenCalled(
      () ==> {
        $req->at('one');
      },
    )->willNotThrow();

    $values = $req->at('one');
    $assert->int($values->count())->eq($expectedValues->count());
    foreach ($values as $index => $value) {
      $assert->string($value)->is($expectedValues->at($index));
    }
  }

  <<Test>>
  public function optionWithMissingValue1(Assert $assert): void {
    $req = (new Request('', Vector {'-o'}, '', $this->parser));

    $assert->whenCalled(
      () ==> {
        $req->withOptions(
          Vector {
            shape(
              'name' => 'one',
              'alias' => 'o',
              'value required' => true,
              'description' => '',
            ),
          },
        );
      },
    )->willThrowClass(
      \HackPack\HackMini\Command\Exception\MissingValue::class,
    );
  }

  <<Test>>
  public function optionWithMissingValue2(Assert $assert): void {
    $req = (new Request('', Vector {'-oa=stuff'}, '', $this->parser));

    $assert->whenCalled(
      () ==> {
        $req->withOptions(
          Vector {
            shape(
              'name' => 'one',
              'alias' => 'o',
              'value required' => true,
              'description' => '',
            ),
          },
        );
      },
    )->willThrowClass(
      \HackPack\HackMini\Command\Exception\MissingValue::class,
    );
  }

  <<Test>>
  public function optionWithMissingValue4(Assert $assert): void {
    $req = (new Request('', Vector {'-one=stuff'}, '', $this->parser));

    $assert->whenCalled(
      () ==> {
        $req->withOptions(
          Vector {
            shape(
              'name' => 'one',
              'alias' => 'o',
              'value required' => true,
              'description' => '',
            ),
          },
        );
      },
    )->willThrowClass(
      \HackPack\HackMini\Command\Exception\MissingValue::class,
    );
  }

  <<Test>>
  public function optionNameAsSubstring(Assert $assert): void {
    $req =
      (new Request('', Vector {'--onea=stuff'}, '', $this->parser))
        ->withOptions(
          Vector {
            shape(
              'name' => 'one',
              'alias' => 'o',
              'value required' => true,
              'description' => '',
            ),
          },
        );

    $assert->whenCalled(
      () ==> {
        $req->at('one');
      },
    )->willThrowClass(
      \HackPack\HackMini\Command\Exception\MissingInput::class,
    );
  }
}
