<?hh // strict

namespace HackPack\HackMini\Test\Doubles;

use HackPack\HackMini\Filter\Filter;

class FilterSpy<Tval> implements Filter<Tval> {
  public (function(string): string) $description;
  public (function(mixed): bool) $filter;
  public (function(mixed): Tval) $transform;

  public function __construct(
    (function(string): string) $description,
    (function(mixed): bool) $filter,
    (function(mixed): Tval) $transform,
  ) {
    $this->description = $description;
    $this->filter = $filter;
    $this->transform = $transform;
  }

  public function description(string $name): string {
    $f = $this->description;
    return $f($name);
  }

  public function validate(mixed $data): bool {
    $f = $this->filter;
    return $f($data);
  }

  public function transform(mixed $data): Tval {
    $f = $this->transform;
    return $f($data);
  }
}
