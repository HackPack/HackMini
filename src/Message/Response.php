<?hh // strict

namespace HackPack\HackMini\Message;
use HackPack\HackMini\Contract\Message\Stream;

final class Response {
  use Message;

  <<Provides('ServerResponse')>>
  public static function provider(\FactoryContainer $c): this {
    return self::factory();
  }

  public static function factory(): this {
    return new static(
      HttpProtocolVersion::v11,
      'OK',
      200,
      Uri::fromString(''),
      Map {},
      Map {},
      new StringBody(''),
    );
  }

  public function __construct(
    private HttpProtocolVersion $protocolVersion,
    private string $reason,
    private int $status,
    private Uri $uri,
    private Map<string, string> $headers,
    private Map<string, Cookie> $cookies,
    private Stream $body,
  ) {}

  /**
   * Set the body to the contents of the string given
   */
  public function show(string $body): this {
    return $this->withBody(new StringBody($body));
  }

  /**
   * Set the HTTP status code to 401
   */
  public function notAuthorized(): this {
    return $this->withStatus(401);
  }

  public function notFound(): this {
    return $this->withStatus(404);
  }

  public function forward(string $uri): this {
    return $this->withHeader('Location', $uri)->withStatus(303);
  }

  public function forbidden(): this {
    return $this->withStatus(403);
  }

  public function getStatusCode(): int {
    return $this->status;
  }

  public function withStatus(int $code, string $reasonPhrase = ''): this {
    $new = clone $this;
    $new->status = $this->validateStatusCode($code);
    $new->reason = (string) $reasonPhrase;
    return $new;
  }

  private function validateStatusCode(?int $code): int {
    // TODO: use a lookup table mapping codes to standard phrases
    if ($code === null || $code < 100 || 600 <= $code) {
      throw new \InvalidArgumentException(
        'Status code must be between 100 and 599',
      );
    }
    return $code;
  }

  public function getReasonPhrase(): string {
    return $this->reason;
  }
}
