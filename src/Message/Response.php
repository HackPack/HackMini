<?hh // strict

namespace HackPack\HackMini\Message;
use Psr\Http\Message\ResponseInterface;

class Response implements ResponseInterface
{
    use Message;

    public function __construct(
        private HttpProtocolVersion $protocolVersion,
        private string $reason,
        private int $status,
        private Uri $uri,
        private Map<string, Vector<string>> $headerValues,
        private Map<string, string> $headerKeys,
        Body $body,
    )
    {
        $this->body = $body;
    }

    /**
     * Set the body to the contents of the string given
     */
    public function show(string $body) : this
    {
        return $this;
    }

    /**
     * Set the HTTP status code to 401
     */
    public function notAuthorized() : this
    {
        return $this;
    }

    public function getStatusCode() : int
    {
        return $this->status;
    }

    public function withStatus(int $code, string $reasonPhrase = '') : this
    {
        $new = clone $this;
        $new->status = $this->validateStatusCode($code);
        $new->reason = $reasonPhrase;
        return $new;
    }

    private function validateStatusCode(int $code) : int
    {
        // TODO: use a lookup table mapping codes to standard phrases
        if($code < 100 || 600 <= $code) {
             throw new \InvalidArgumentException('Status code must be between 100 and 599');
        }
        return $code;
    }

    public function getReasonPhrase() : string
    {
        return $this->reason;
    }
}
