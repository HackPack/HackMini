<?hh // strict

namespace HackPack\HackMini\Validator;

interface Validator<Tval>
{
    /**
     * Validate/filter the raw input, returning null if validation fails
     */
    public function get(mixed $raw) : ?Tval;

    /**
     * Validate/filter the raw input, throwing an exception if validation fails
     */
    public function at(mixed $raw) : Tval;

    /**
     * Public facing description of what the validator is checking.
     * Should be sufficient as a form error message.
     *
     * @param string $name The name of the field being validated
     */
    public function description(string $name) : string;
}
