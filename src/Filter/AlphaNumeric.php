<?hh // strict

namespace HackPack\HackMini\Filter;

class AlphaNumeric implements Filter<string> {

  const PATTERN = '/[^0-9]*/';

  public function description(string $name): string {
    return $name.' must be a string and contain only numbers and letters';
  }

  public function validate(mixed $raw): bool {
    return is_string($raw);
  }

  public function transform(mixed $raw): string {
    if (!is_string($raw)) {
      return '';
    }

    return preg_replace('/[^0-9a-zA-Z]*/', '', $raw);
  }
}
