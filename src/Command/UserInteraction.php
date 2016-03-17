<?hh // strict

namespace HackPack\HackMini\Command;

use Psr\Http\Message\StreamInterface;

<<__ConsistentConstruct>>
class UserInteraction
{
    public function __construct(private StreamInterface $stream)
    {
        if(!$stream->isReadable() || !$stream->isWritable()) {
            throw new \InvalidArgumentException(
                'The stream implementation passed to UserInteraction must' .
                'be readable and writable.'
            );
        }
    }

    public function show(string $message) : void
    {
        $this->stream->write($message);
    }

    public function showLine(string $message) : void
    {
        $this->stream->write($message . PHP_EOL);
    }
}
