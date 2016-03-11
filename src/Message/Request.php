<?hh // strict

namespace HackPack\HackMini\Message;

use HackPack\HackMini\Validator\Validator;
use FactoryContainer;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

final class Request implements RequestInterface
{
    use Message;

    private Vector<string> $pathGroups = Vector{};
    private Map<string, mixed> $parsedBody = Map{};
    private string $target;

    public function __construct(
        private HttpProtocolVersion $protocolVersion,
        private string $method,
        private Uri $uri,
        private Map<string, Vector<string>> $headerValues,
        private Map<string, string> $headerKeys,
        Body $body,
    )
    {
        $this->body = $body;
        $this->target = $uri->getPath() . $uri->getQueryWithQuestion();
    }

    public function getRestMethod() : RestMethod
    {
        $method = RestMethod::coerce($this->method);
        return $method === null ?
            RestMethod::Unknown :
            $method;
    }

    public function getMethod() : string
    {
        return $this->method;
    }

    public function withMethod(string $verb) : this
    {
        $new = clone $this;
        $new->method = $verb;
        return $new;
    }


    public function get<Tval>(string $name, Validator<Tval> $validator) : ?Tval
    {
        $raw = $this->parsedBody->get($name);
        if($raw === null) {
            return null;
        }
        return $validator->get($raw);
    }

    public function at<Tval>(string $name, Validator<Tval> $validator) : Tval
    {
        $raw = $this->parsedBody->get($name);
        if($raw === null) {
            throw MissingInput::build($name);
        }
        return $validator->at($raw);
    }

    public function pathGroup(int $offset) : string
    {
        $group = $this->pathGroups->get($offset);
        if($group === null) {
            throw new PathGroupNotFound();
        }
        return $group;
    }

    public function setPathGroups(Traversable<string> $groups) : this
    {
        $new = clone $this;
        $new->pathGroups = new Vector($groups);
        return $new;
    }

    public function getRequestTarget() : string
    {
        return $this->target;
    }

    public function withRequestTarget(mixed $target) : this
    {
        if($target instanceof UriInterface) {
            $target = (string)$target;
        }
        if(!is_string($target)) {
            throw new \InvalidArgumentException('The request target must be a string or an instance of UriInterface');
        }

        $new = clone $this;
        $new->target = $target;
        return $new;
    }

    public function getUri() : Uri
    {
        return $this->uri;
    }

    public function withUri(UriInterface $uri, bool $preserveHost = false) : this
    {
        $new = clone $this;
        $new->uri = Uri::fromPsr($uri);

        $currentHeader = $new->getHeaderLine('Host');
        if(
            // Don't touch the host header
            $preserveHost &&
            // Unless we don't have one
            ($currentHeader === null || $currentHeader === '')
        ) {
            return $new;
        }

        // Update the host header if the new uri has one
        if($uri->getHost() !== '') {
            return $new->withHeader('Host', $uri->getHost());
        }

        return $new;
    }
}
