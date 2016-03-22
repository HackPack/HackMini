<?hh // strict

namespace HackPack\HackMini\Command;

use HackPack\HackMini\Contract\Command\Reader;
use HackPack\HackMini\Contract\Command\Writer;

<<__ConsistentConstruct>>
class UserInteraction
{
    public function __construct(
        private Reader $reader,
        private Writer $writer,
    )
    {
    }

    public function show(string $message) : void
    {
        $this->writer->write($message);
    }

    public function showLine(string $message) : void
    {
        $this->writer->write($message . PHP_EOL);
    }

    public function prompt(string $prompt = '') : string
    {
        $this->writer->write($prompt);
        return $this->reader->readline();
    }

    public function showList(Traversable<string> $list) : void
    {
        foreach($list as $line) {
            $this->showLine($line);
        }
    }
}
