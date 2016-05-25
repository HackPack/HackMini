<?hh // strict

namespace HackPack\HackMini\Filter;

class Email implements Filter<string> {

  public function description(string $name): string {
    return $name.' must be a string containing a valid email address.';
  }

  public function validate(mixed $raw): bool {
    return filter_var($raw, FILTER_VALIDATE_EMAIL) !== false;
  }

  public function transform(mixed $raw): string {
    return $this->validate($raw) ? (string) $raw : '';
  }
}
