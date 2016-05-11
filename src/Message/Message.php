<?hh // strict

namespace HackPack\HackMini\Message;

use HackPack\HackMini\Contract\Message\Stream;

trait Message {
  private HttpProtocolVersion $protocolVersion;
  private Map<string, Vector<string>> $headerValues;
  private Map<string, string> $headerKeys;
  private Stream $body;

  /**
   * Retrieves the HTTP protocol version as a string.
   *
   * The string MUST contain only the HTTP version number (e.g., "1.1", "1.0").
   *
   * @return string HTTP protocol version.
   */
  public function getProtocolVersion(): string {
    return $this->protocolVersion;
  }

  /**
   * Return an instance with the specified HTTP protocol version.
   *
   * The version string MUST contain only the HTTP version number (e.g.,
   * "1.1", "1.0").
   *
   * This method MUST be implemented in such a way as to retain the
   * immutability of the message, and MUST return an instance that has the
   * new protocol version.
   *
   * @param string $version HTTP protocol version
   * @return self
   */
  public function withProtocolVersion(string $version): this {
    $new = clone $this;
    $newVersion = HttpProtocolVersion::coerce($version);

    if ($newVersion !== null) {
      $new->protocolVersion = $newVersion;
    }

    return $new;
  }

  /**
   * Retrieves all message header values.
   *
   * The keys represent the header name as it will be sent over the wire, and
   * each value is an array of strings associated with the header.
   *
   *     // Represent the headers as a string
   *     foreach ($message->getHeaders() as $name => $values) {
   *         echo $name . ": " . implode(", ", $values);
   *     }
   *
   *     // Emit headers iteratively:
   *     foreach ($message->getHeaders() as $name => $values) {
   *         foreach ($values as $value) {
   *             header(sprintf('%s: %s', $name, $value), false);
   *         }
   *     }
   *
   * While header names are not case-sensitive, getHeaders() will preserve the
   * exact case in which headers were originally specified.
   *
   * @return array Returns an associative array of the message's headers. Each
   *     key MUST be a header name, and each value MUST be an array of strings
   *     for that header.
   */
  public function getHeaders(): array<string, array<string>> {
    $headers = [];
    foreach ($this->headerValues as $name => $values) {
      $headers[$this->headerKeys->at($name)] = $values->toArray();
    }
    return $headers;
  }

  /**
   * Checks if a header exists by the given case-insensitive name.
   *
   * @param string $name Case-insensitive header field name.
   * @return bool Returns true if any header names match the given header
   *     name using a case-insensitive string comparison. Returns false if
   *     no matching header name is found in the message.
   */
  public function hasHeader(string $name): bool {
    return $this->headerValues->containsKey(strtolower($name));
  }

  /**
   * Retrieves a message header value by the given case-insensitive name.
   *
   * This method returns an array of all the header values of the given
   * case-insensitive header name.
   *
   * If the header does not appear in the message, this method MUST return an
   * empty array.
   *
   * @param string $name Case-insensitive header field name.
   * @return string[] An array of string values as provided for the given
   *    header. If the header does not appear in the message, this method MUST
   *    return an empty array.
   */
  public function getHeader(string $name): array<string> {
    $values = $this->headerValues->get(strtolower($name));
    if ($values === null) {
      return [];
    }
    return $values->toArray();
  }

  /**
   * Retrieves a comma-separated string of the values for a single header.
   *
   * This method returns all of the header values of the given
   * case-insensitive header name as a string concatenated together using
   * a comma.
   *
   * NOTE: Not all header values may be appropriately represented using
   * comma concatenation. For such headers, use getHeader() instead
   * and supply your own delimiter when concatenating.
   *
   * If the header does not appear in the message, this method MUST return
   * an empty string.
   *
   * @param string $name Case-insensitive header field name.
   * @return string A string of values as provided for the given header
   *    concatenated together using a comma. If the header does not appear in
   *    the message, this method MUST return an empty string.
   */
  public function getHeaderLine(string $name): string {
    return implode(',', $this->getHeader($name));
  }

  public function withHeader(string $name, string $value): this {
    $new = clone $this;
    $new->setHeaderList($name, Vector {$value});
    return $new;
  }

  public function withHeaderList(
    string $name,
    \ConstVector<string> $values,
  ): this {
    $new = clone $this;
    $new->setHeaderList($name, $values);
    return $new;
  }
  private function setHeaderList(
    string $name,
    \ConstVector<string> $values,
  ): void {

    $lowerName = strtolower($name);
    if (strpos(':', $lowerName) !== -1) {
      throw new \InvalidArgumentException(
        'Header names may not contain the ":" character.',
      );
    }

    $this->headerKeys->set($lowerName, $name);
    $this->headerValues->set($lowerName, $values->toVector());
  }

  public function withAddedHeader(string $name, string $value): this {
    $new = clone $this;
    $new->addHeaderList($name, Vector {$value});
    return $new;
  }

  public function withAddedHeaderList(
    string $name,
    \ConstVector<string> $values,
  ): this {
    $new = clone $this;
    $new->addHeaderList($name, $values);
    return $new;
  }

  private function addHeaderList(
    string $name,
    \ConstVector<string> $values,
  ): void {
    $lowerName = strtolower($name);
    $currentValues = $this->headerValues->get($lowerName);
    if ($currentValues === null) {
      $currentValues = Vector {};
      $this->headerValues->set($lowerName, $currentValues);
      $this->headerKeys->set($lowerName, $name);
    }

    $currentValues->addAll($values);
  }

  /**
   * Return an instance without the specified header.
   *
   * Header resolution MUST be done without case-sensitivity.
   *
   * This method MUST be implemented in such a way as to retain the
   * immutability of the message, and MUST return an instance that removes
   * the named header.
   *
   * @param string $name Case-insensitive header field name to remove.
   * @return self
   */
  public function withoutHeader(string $name): this {
    $lowerName = strtolower($name);
    $new = clone $this;
    $new->headerValues->removeKey($lowerName);
    $new->headerKeys->removeKey($lowerName);
    return $new;
  }

  /**
   * Gets the body of the message.
   *
   * @return StreamInterface Returns the body as a stream.
   */
  public function getBody(): Stream {
    return $this->body;
  }

  /**
   * Return an instance with the specified message body.
   *
   * The body MUST be a StreamInterface object.
   *
   * This method MUST be implemented in such a way as to retain the
   * immutability of the message, and MUST return a new instance that has the
   * new body stream.
   *
   * @param StreamInterface $body Body.
   * @return self
   * @throws \InvalidArgumentException When the body is not valid.
   */
  public function withBody(Stream $body): this {
    $new = clone $this;
    $new->body = $body;
    return $new;
  }

  /**
   * @throws \InvalidArgumentException when any value is not a string
   */
  private function stringifyValues(mixed $value): Vector<string> {
    $rawValues = is_array($value) ? new Vector($value) : Vector {$value};
    return $rawValues->map(
      $v ==> {
        if (is_string($v)) {
          return $v;
        }
        throw new \InvalidArgumentException(
          'Header values must be strings.',
        );
      },
    );
  }
}
