<?hh // strict

namespace HackPack\HackMini\Filter;

class ListFilter<Tval> implements Filter<\ConstVector<Tval>> {

  public function __construct(private Filter<Tval> $elementFilter) {}

  public function description(string $name): string {
    return $this->elementFilter->description('elements of '.$name);
  }

  public function validate(mixed $raw): bool {
    if (!($raw instanceof Traversable)) {
      return false;
    }
    foreach ($raw as $element) {
      if (!$this->elementFilter->validate($element)) {
        return false;
      }
    }
    return true;
  }

  public function transform(mixed $raw): \ConstVector<Tval> {
    if (!($raw instanceof Traversable)) {
      return Vector {};
    }

    return
      (new Vector($raw))
        ->map($element ==> $this->elementFilter->transform($element));
  }
}
