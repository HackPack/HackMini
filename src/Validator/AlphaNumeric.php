<?hh // strict

namespace HackPack\HackMini\Validator;

class AlphaNumeric implements Validator<string>
{
    public function description(string $name) : string
    {
        return $name . ' must contain only numbers and letters';
    }

    public function get(mixed $raw) : ?string
    {
        return '';
    }

    public function at(mixed $raw) : string
    {
        return '';
    }
}
