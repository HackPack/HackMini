<?hh // strict

namespace HackPack\HackMini\Test\Filter;

use HackPack\HackMini\Filter\AlphaNumeric;
use HackPack\HackUnit\Contract\Assert;

type AlphaNumericFilterData = shape(
  'raw' => mixed,
  'valid' => bool,
  'transformed' => string,
);

class AlphaNumericTest {
  <<DataProvider('data')>>
  public static function data(): Traversable<AlphaNumericFilterData> {
    return [
      shape('raw' => 'abc123', 'transformed' => 'abc123', 'valid' => true),
      shape('raw' => 'abc^123', 'transformed' => 'abc123', 'valid' => true),
      shape(
        'raw' => '<tag>abc</tag>',
        'transformed' => 'tagabctag',
        'valid' => true,
      ),
      shape('raw' => null, 'transformed' => '', 'valid' => false),
      shape('raw' => [], 'transformed' => '', 'valid' => false),
      shape('raw' => true, 'transformed' => '', 'valid' => false),
      shape('raw' => 1, 'transformed' => '', 'valid' => false),
      shape('raw' => new self(), 'transformed' => '', 'valid' => false),
    ];
  }

  <<Test, Data('data')>>
  public function doesValidate(
    Assert $assert,
    AlphaNumericFilterData $data,
  ): void {
    $filter = new AlphaNumeric();
    $assert->bool($filter->validate($data['raw']))->is($data['valid']);
    $assert->string($filter->transform($data['raw']))
      ->is($data['transformed']);
  }
}
