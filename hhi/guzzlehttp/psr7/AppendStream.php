<?hh // decl
namespace GuzzleHttp\Psr7;

use Psr\Http\Message\StreamInterface;

/**
 * Reads from multiple streams, one after the other.
 *
 * This is a read-only stream decorator.
 */
class AppendStream implements StreamInterface
{

    /**
     * @param StreamInterface[] $streams Streams to decorate. Each stream must
     *                                   be readable.
     */
    public function __construct(array<StreamInterface> $streams = []): void
    {
    }

    public function __toString(): string
    {
    }

    /**
     * Add a stream to the AppendStream
     *
     * @param StreamInterface $stream Stream to append. Must be readable.
     *
     * @throws \InvalidArgumentException if the stream is not readable
     */
    public function addStream(StreamInterface $stream): void
    {
    }

    public function getContents(): string
    {
    }

    /**
     * Closes each attached stream.
     *
     * {@inheritdoc}
     */
    public function close(): void
    {
    }

    /**
     * Detaches each attached stream
     *
     * {@inheritdoc}
     */
    public function detach(): ?resource
    {
    }

    public function tell(): int
    {
    }

    /**
     * Tries to calculate the size by adding the size of each stream.
     *
     * If any of the streams do not return a valid number, then the size of the
     * append stream cannot be determined and null is returned.
     *
     * {@inheritdoc}
     */
    public function getSize(): int
    {
    }

    public function eof(): bool
    {
    }

    public function rewind(): void
    {
    }

    /**
     * Attempts to seek to the given position. Only supports SEEK_SET.
     *
     * {@inheritdoc}
     */
    public function seek(int $offset, $whence = SEEK_SET): void
    {
    }

    /**
     * Reads from all of the appended streams until the length is met or EOF.
     *
     * {@inheritdoc}
     */
    public function read(int $length): string
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

    public function write(string $string): int
    {
    }

    public function getMetadata(?string $key = null): mixed
    {
    }
}
