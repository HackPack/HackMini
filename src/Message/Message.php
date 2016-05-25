<?hh // strict

namespace HackPack\HackMini\Message;

use HackPack\HackMini\Contract\Message\Stream;

type Cookie = shape(
  'name' => string,
  'payload' => string,
  'expires' => ?\DateTime,
  'secure' => ?bool,
  'http only' => ?bool,
  'path' => ?string,
  'domain' => ?string,
);

trait Message {
  private HttpProtocolVersion $protocolVersion;
  private Map<string, string> $headers;
  private Map<string, Cookie> $cookies;
  private Stream $body;

  public function getProtocolVersion(): string {
    return $this->protocolVersion;
  }

  public function withProtocolVersion(HttpProtocolVersion $version): this {
    $new = clone $this;
    $new->protocolVersion = $version;
    return $new;
  }

  public function getHeaders(): \ConstMap<string, string> {
    return $this->headers;
  }

  public function hasHeader(string $name): bool {
    return $this->headers->containsKey(mb_strtolower($name));
  }

  public function getHeader(string $name): ?string {
    return $this->headers->get(mb_strtolower($name));
  }

  public function withHeader(string $name, string $value): this {
    $new = clone $this;
    $new->headers->set(mb_strtolower($name), $value);
    return $new;
  }

  public function withAddedHeader(
    string $name,
    string $value,
    string $seperator = ',',
  ): this {
    $new = clone $this;
    $lowerName = mb_strtolower($name);
    $oldHeader = $this->headers->get($lowerName);
    $newHeader = $oldHeader === null ? $value : $oldHeader.$seperator.$value;
    $new->headers->set($lowerName, $newHeader);
    return $new;
  }

  public function withoutHeader(string $name): this {
    $lowerName = mb_strtolower($name);
    $new = clone $this;
    $new->headers->removeKey($lowerName);
    return $new;
  }

  public function getBody(): Stream {
    return $this->body;
  }

  public function withBody(Stream $body): this {
    $new = clone $this;
    $new->body = $body;
    return $new;
  }

  public function withCookie(Cookie $cookie): this {
    $new = clone $this;
    $cookie['name'] = mb_strtolower($cookie['name']);
    $new->cookies->set($cookie['name'], $cookie);
    return $new;
  }

  public function getCookie(string $name): ?string {
    $cookie = $this->cookies->get(mb_strtolower($name));
    return $cookie === null ? null : $cookie['name'];
  }

  public function getCookies(): \ConstVector<Cookie> {
    return $this->cookies->values();
  }
}
