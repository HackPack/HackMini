<?hh // strict

namespace HackPack\HackMini\Filter;

interface Filter<Tval> {

  /**
   * Validate the value
   */
  public function validate(mixed $raw): bool;

  /**
   * Transform the raw value into a concrete data type
   */
  public function transform(mixed $raw): Tval;

  /**
   * Public facing description of what the validator is checking.
   * Should be sufficient as a form error message.
   *
   * @param string $name The name of the field being validated
   */
  public function description(string $name): string;
}
