<?hh // strict

namespace HackPack\HackMini\Message;

use HackPack\HackMini\Validator\Validator;
use HackPack\HackMini\Contract\Message\Stream;
use FactoryContainer;

final class Request {
  use Message;

  private Vector<string> $pathGroups = Vector {};
  private Map<string, mixed> $parsedBody = Map {};

  public function __construct(
    private HttpProtocolVersion $protocolVersion,
    private RestMethod $method,
    private Uri $uri,
    KeyedTraversable<string, string> $headers,
    Stream $body,
  ) {
    $this->body = $body;
    $this->headerKeys = Map {};
    $this->headerValues = Map {};
    foreach ($headers as $name => $value) {
      $this->addHeaderList($name, Vector {$value});
    }
  }

  public function getMethod(): RestMethod {
    return $this->method;
  }

  public function withMethod(RestMethod $verb): this {
    $new = clone $this;
    $new->method = $verb;
    return $new;
  }

  public function get<Tval>(string $name, Validator<Tval> $validator): ?Tval {
    $raw = $this->parsedBody->get($name);
    if ($raw === null) {
      return null;
    }
    return $validator->get($raw);
  }

  public function at<Tval>(string $name, Validator<Tval> $validator): Tval {
    $raw = $this->parsedBody->get($name);
    if ($raw === null) {
      throw MissingInput::build($name);
    }
    return $validator->at($raw);
  }


  public function pathGroup(int $offset): string {
    $group = $this->pathGroups->get($offset);
    if ($group === null) {
      throw new PathGroupNotFound();
    }
    return $group;
  }

  public function pathGroups(): \ConstVector<string> {
    return $this->pathGroups;
  }
  
  public function withPathGroups(Traversable<string> $groups): this {
    $new = clone $this;
    $new->pathGroups = new Vector($groups);
    return $new;
  }

  public function getUri(): Uri {
    return $this->uri;
  }

  public function withUri(Uri $uri, bool $preserveHost = false): this {
    $new = clone $this;
    $new->uri = $uri;

    $currentHeader = $new->getHeaderLine('Host');
    if (// Don't touch the host header
        $preserveHost &&
        // Unless we don't have one
        $currentHeader !== null &&
        $currentHeader !== '') {
      return $new;
    }

    // Update the host header if the new uri has one
    if ($uri->getHost() !== '') {
      return $new->withHeader('Host', $uri->getHost());
    }

    return $new;
  }
}
