<?hh // strict

namespace HackPack\HackMini\Exception;

class CircularDependency extends HackMini
{
    public function __construct(string $name, Vector<string> $factoryList)
    {
        parent::__construct(sprintf('Circular dependency found: %s->%s', implode('->', $factoryList), $name));
    }
}
