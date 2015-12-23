<?hh // decl
namespace GuzzleHttp\Psr7;

use Psr\Http\Message\StreamInterface;

/**
 * Stream decorator that begins dropping data once the size of the underlying
 * stream becomes too full.
 */
class DroppingStream implements StreamInterface
{
    use StreamDecoratorTrait;

    /**
     * @param StreamInterface $stream    Underlying stream to decorate.
     * @param int             $maxLength Maximum size before dropping data.
     */
    public function __construct(StreamInterface $stream, int $maxLength)
    {
    }

    public function write(string $string): int
    {
    }
}
