<?hh // strict

namespace HackPack\HackMini\Filter;

class Unsafe implements Filter<mixed> {

  public function description(string $name): string {
    return $name.' may be anything.';
  }

  public function validate(mixed $raw): bool {
    return true;
  }

  public function transform(mixed $raw): mixed {
    return $raw;
  }
}
