<?hh // decl
namespace GuzzleHttp\Psr7;

use Psr\Http\Message\StreamInterface;

/**
 * Provides a buffer stream that can be written to to fill a buffer, and read
 * from to remove bytes from the buffer.
 *
 * This stream returns a "hwm" metadata value that tells upstream consumers
 * what the configured high water mark of the stream is, or the maximum
 * preferred size of the buffer.
 */
class BufferStream implements StreamInterface
{

    /**
     * @param int $hwm High water mark, representing the preferred maximum
     *                 buffer size. If the size of the buffer exceeds the high
     *                 water mark, then calls to write will continue to succeed
     *                 but will return false to inform writers to slow down
     *                 until the buffer has been drained by reading from it.
     */
    public function __construct(int $hwm = 16384)
    {
    }

    public function __toString(): string
    {
    }

    public function getContents(): string
    {
    }

    public function close(): void
    {
    }

    public function detach(): void
    {
    }

    public function getSize(): int
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

    public function seek(int $offset, $whence = SEEK_SET): void
    {
    }

    public function eof(): bool
    {
    }

    public function tell(): int
    {
    }

    /**
     * Reads data from the buffer.
     */
    public function read(int $length): string
    {
    }

    /**
     * Writes data to the buffer.
     */
    public function write(string $string): int
    {
    }

    public function getMetadata($key = null): mixed
    {
    }
}
