<?hh // strict

namespace HackPack\HackMini\Test\Filter;

use HackPack\HackMini\Filter\ListFilter;
use HackPack\HackMini\Test\Doubles\FilterSpy;
use HackPack\HackUnit\Contract\Assert;

class ListFilterTest {

  private Vector<mixed> $filterCalls = Vector {};
  private Vector<mixed> $transformCalls = Vector {};
  private Vector<string> $nameCalls = Vector {};
  private FilterSpy<mixed> $spy;

  public function __construct() {
    $this->spy = new FilterSpy(
      $name ==> {
        $this->nameCalls->add($name);
        return $name;
      },
      $data ==> {
        $this->filterCalls->add($data);
        return true;
      },
      $data ==> {
        $this->transformCalls->add($data);
        return $data;
      },
    );
  }

  <<DataProvider('scalars')>>
  public static function scalars(): Traversable<mixed> {
    return ['a', 1, 0.5, new self(), true];
  }

  <<Test, Data('scalars')>>
  public function scalarFailures(Assert $assert, mixed $data): void {
    $filter = new ListFilter($this->spy);

    $assert->bool($filter->validate($data))->is(false);
    $result = $filter->transform($data);
    $assert->mixed($result)->isTypeOf(Vector::class);
    invariant($result instanceof Vector, 'Tell the type checker we know its a vector.');
    $assert->container($result)->isEmpty();
    $assert->container($this->filterCalls)->isEmpty();
    $assert->container($this->transformCalls)->isEmpty();
  }

  <<Test>>
  public function elementFilterIsApplied(Assert $assert): void {
    $data = ['a', 1, 0.4, [3, 2]];
    $filter = new ListFilter($this->spy);

    $assert->bool($filter->validate($data))->is(true);
    $assert->container($this->filterCalls)->containsOnly($data);
  }

  <<Test>>
  public function elementFilterResultIsRespected(Assert $assert): void {
    $data = ['a', 'b', 'c'];
    $this->spy->filter = $raw ==> {
      if($this->filterCalls->count() > 0) {
        return false;
      }
      $this->filterCalls->add($raw);
      return true;
    };

    $filter = new ListFilter($this->spy);

    $assert->bool($filter->validate($data))->is(false);
    $assert->container($this->filterCalls)->containsOnly(['a']);
  }

  <<Test>>
  public function elementFilterDescriptionIsIncluded(Assert $assert): void {
    $elementDescription = 'Element description.';
    $this->spy->description = $name ==> {
      $this->nameCalls->add($name);
      return $elementDescription;
    };

    $filter = new ListFilter($this->spy);

    $assert->string($filter->description('field name'))->contains($elementDescription);
    $assert->int($this->nameCalls->count())->eq(1);
    $assert->string($this->nameCalls->at(0))->contains('field name');
  }

  <<Test>>
  public function elementTransformIsApplied(Assert $assert): void {
    $data = ['a', 'b', ['c', 'd']];
    $filter = new ListFilter($this->spy);
    $filter->transform($data);
    $assert->container($this->transformCalls)->containsOnly($data);
  }

  <<Test>>
  public function elementTransformIsRespected(Assert $assert): void {
    $data = ['a', 'b', 'c'];
    $this->spy->transform = $raw ==> {
      $this->transformCalls->add($raw);
      return $this->transformCalls->count();
    };
    $filter = new ListFilter($this->spy);

    $assert->container($filter->transform($data))->containsOnly([1, 2, 3]);
  }
}
