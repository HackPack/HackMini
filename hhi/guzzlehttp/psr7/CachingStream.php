<?hh // decl
namespace GuzzleHttp\Psr7;

use Psr\Http\Message\StreamInterface;

/**
 * Stream decorator that can cache previously read bytes from a sequentially
 * read stream.
 */
class CachingStream implements StreamInterface
{
    use StreamDecoratorTrait;

    /**
     * We will treat the buffer object as the body of the stream
     *
     * @param StreamInterface $stream Stream to cache
     * @param StreamInterface $target Optionally specify where data is cached
     */
    public function __construct(
        StreamInterface $stream,
        ?StreamInterface $target = null
    ) {
    }

    public function getSize(): int
    {
    }

    public function rewind(): void
    {
    }

    public function seek(int $offset, $whence = SEEK_SET): void
    {
    }

    public function read(int $length): string
    {
    }

    public function write(string $string): int
    {
    }

    public function eof(): bool
    {
    }

    /**
     * Close both the remote stream and buffer stream
     */
    public function close(): void
    {
        $this->remoteStream->close() && $this->stream->close();
    }

}
