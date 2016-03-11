<?hh // strict

namespace HackPack\HackMini\Command;

final class Builder
{
    public function __construct(
        private \ConstVector<ParsedDefinition> $definitions,
    ) { }

    public function build() : string
    {
        return '';
    }
}
