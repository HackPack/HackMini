<?hh // strict

namespace HackPack\HackMini\Message;

use HackPack\HackMini\Validator\Validator;

final class MissingInput extends \Exception
{
    public static function build(string $name) : this
    {
        return new static(
            $name . ' is missing',
            $name,
        );
    }

    public function __construct(
        string $message,
        private string $name,
    ) {
        parent::__construct($message);
    }

    public function paramName() : string
    {
        return $this->name;
    }
}
