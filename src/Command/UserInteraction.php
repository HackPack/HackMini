<?hh // strict

namespace HackPack\HackMini\Command;

<<__ConsistentConstruct>>
class UserInteraction
{
    public static function fromEnv() : this
    {
        return new static();
    }

    public function show(string $message) : void
    {
    }

    public function showLine(string $message) : void
    {
    }
}
