<?hh // strict

namespace HackPack\HackMini\Message;

use Psr\Http\Message\UriInterface;

// TODO: ensure this complies with the PSR-7 standard
final class Uri implements UriInterface {
  public static function fromPsr(UriInterface $other): this {
    // TODO: be more efficient about this
    return self::fromString((string) $other);
  }

  public static function fromString(string $uri): this {
    $port = null;
    $parts = Map {};

    foreach (parse_url($uri) as $part => $value) {
      if (is_string($part) && is_string($value)) {
        $parts->set($part, $value);
      }

      if ($part === 'port' && is_int($value)) {
        $port = $value;
      }
    }

    $scheme = $parts->get('scheme') === null ? '' : $parts->at('scheme');
    $user = $parts->get('user');
    $password = $parts->get('pass');
    $host = $parts->get('host') === null ? '' : $parts->at('host');
    $path = $parts->get('path') === null ? '' : $parts->at('path');
    $query = $parts->get('query') === null ? '' : $parts->at('query');
    $fragment =
      $parts->get('fragment') === null ? '' : $parts->at('fragment');

    return new static(
      $scheme,
      $user,
      $password,
      $port,
      $host,
      $path,
      $query,
      $fragment,
    );
  }

  public function __construct(
    private string $scheme,
    private ?string $user,
    private ?string $password,
    private ?int $port,
    private string $host,
    private string $path,
    private string $query,
    private string $fragment,
  ) {}

  public function getScheme(): string {
    return $this->scheme;
  }

  public function getAuthority(): string {
    $user = $this->getUserInfo();
    if ($user !== '') {
      $user .= '@';
    }

    $port = $this->port === null ? '' : ':'.$this->port;
    return $user.$this->host.$port;
  }

  public function getUserInfo(): string {
    $user = '';
    if ($this->user !== null) {
      $user = $this->user;
    }
    if ($this->password !== null) {
      $user .= ':'.$this->password;
    }
    return $user;
  }

  public function getHost(): string {
    return $this->host;
  }

  public function getPort(): ?int {
    // TODO: return null if port is default for the scheme
    return $this->port;
  }

  public function getPath(): string {
    return $this->path;
  }

  public function getQuery(): string {
    return $this->query;
  }

  public function getFragment(): string {
    return $this->fragment;
  }

  public function withScheme(string $scheme): this {
    $scheme = $this->validateScheme($scheme);
    $new = clone $this;
    $new->scheme = $scheme;
    return $new;
  }

  private function validateScheme(string $scheme): string {
    // TODO: decide which schemes are supported.  Throw \InvalidArgumentException when not.
    if ($scheme === null) {
      throw new \InvalidArgumentException('Scheme may not be null');
    }
    return $scheme;
  }

  public function withUserInfo(string $user, ?string $password = null): this {
    $new = clone $this;
    $new->user = (string) $user;
    $new->password = (string) $password;
    return $new;
  }

  public function withHost(string $host): this {
    $host = $this->validateHost($host);
    $new = clone $this;
    $new->host = $host;
    return $new;
  }

  private function validateHost(string $host): string {
    if ($host === null) {
      throw new \InvalidArgumentException('Host may not be null.');
    }
    return $host;
  }

  public function withPort(?int $port): this {
    $this->validatePort($port);
    $new = clone $this;
    $new->port = $port;
    return $new;
  }

  private function validatePort(?int $port): void {
    if ($port === null) {
      return;
    }
    if ($port < 0 || 65535 < $port) {
      throw new \InvalidArgumentException(
        'Ports must be between 0 and 65535.',
      );
    }
  }

  public function withPath(string $path): this {
    $new = clone $this;
    $new->path = $this->encodePath($path);
    return $new;
  }

  private function encodePath(string $path): string {
    // TODO: ensure the path is correctly encoded
    // TODO: throw \InvalidArgumentException when $path is invalid
    return (string) $path;
  }

  public function withQuery(string $query): this {
    $new = clone $this;
    $new->query = $this->encodeQuery($query);
    return $new;
  }

  private function encodeQuery(string $query): string {
    // TODO: properly encode the query string
    // TODO: throw \InvalidArgumentException for invalid query strings
    return (string) $query;
  }

  public function withFragment(string $fragment): this {
    $new = clone $this;
    $new->fragment = $this->encodeFragment($fragment);
    return $new;
  }

  private function encodeFragment(string $fragment): string {
    // TODO: properly encode fragment
    return (string) $fragment;
  }

  public function getQueryWithQuestion(): string {
    if ($this->query === '') {
      return '';
    }
    return '?'.$this->query;
  }

  public function __toString(): string {
    $scheme = $this->getScheme();
    if ($scheme !== '') {
      $scheme .= ':';
    }

    $authority = $this->getAuthority();
    if ($authority !== '') {
      $authority = '//'.$authority;
    }

    $query = $this->getQueryWithQuestion();

    $fragment = $this->getFragment();
    if ($fragment !== '') {
      $fragment = '#'.$fragment;
    }

    $path = $this->getPath();
    if (substr($path, 0, 1) !== '/' && $authority !== '') {
      $path = '/'.$path;
    }
    if (substr($path, 0, 1) === '/' && $authority === '') {
      $path = '/'.ltrim($path, '/');
    }

    return $scheme.$authority.$path.$fragment;
  }

}
