<?hh // decl
namespace GuzzleHttp\Psr7;

use Psr\Http\Message\StreamInterface;

/**
 * Stream decorator trait
 * @property StreamInterface stream
 */
trait StreamDecoratorTrait
{
    /**
     * @param StreamInterface $stream Stream to decorate
     */
    public function __construct(StreamInterface $stream)
    {
    }

    public function __toString(): string
    {
    }

    public function getContents(): string
    {
    }

    public function close()
    {
    }

    public function getMetadata(?string $key = null): mixed
    {
        return $this->stream->getMetadata($key);
    }

    public function detach(): ?resource
    {
    }

    public function getSize(): int
    {
    }

    public function eof(): bool
    {
    }

    public function tell(): int
    {
    }

    public function isReadable(): bool
    {
    }

    public function isWritable(): bool
    {
    }

    public function isSeekable(): bool
    {
    }

    public function rewind(): void
    {
    }

    public function seek(int $offset, int $whence = SEEK_SET): void
    {
    }

    public function read(int $length): string
    {
    }

    public function write(string $string): int
    {
    }

    /**
     * Implement in subclasses to dynamically create streams when requested.
     *
     * @return StreamInterface
     * @throws \BadMethodCallException
     */
    protected function createStream(): StreamInterface
    {
    }
}
