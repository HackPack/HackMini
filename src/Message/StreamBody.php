<?hh // strict

namespace HackPack\HackMini\Message;

use HackPack\HackMini\Contract\Message\Stream;

class StreamBody implements Stream {
  private ?resource $stream;
  private bool $seekable = false;
  private bool $readable = false;
  private bool $writable = false;

  public function __construct(resource $stream) {
    $this->stream = $stream;
    $metadata = stream_get_meta_data($stream);
    if (array_key_exists('seekable', $metadata)) {
      $this->seekable = (bool) $metadata['seekable'];
    }

    if (array_key_exists('mode', $metadata)) {
      $this->setReadWrite((string) $metadata['mode']);
    }
  }

  /**
   * Reads all data from the stream into a string, from the beginning to end.
   *
   * This method MUST attempt to seek to the beginning of the stream before
   * reading data and read the stream until the end is reached.
   *
   * Warning: This could attempt to load a large amount of data into memory.
   *
   * This method MUST NOT raise an exception in order to conform with PHP's
   * string casting operations.
   *
   * @see http://php.net/manual/en/language.oop5.magic.php#object.tostring
   * @return string
   */
  public function __toString(): string {
    if (!is_resource($this->stream)) {
      return '';
    }

    rewind($this->stream);
    return stream_get_contents($this->stream);
  }

  /**
   * Closes the stream and any underlying resources.
   *
   * @return void
   */
  public function close(): void {
    if (is_resource($this->stream)) {
      fclose($this->stream);
      $this->stream = null;
    }
  }

  /**
   * Get the size of the stream if known.
   *
   * @return int|null Returns the size in bytes if known, or null if unknown.
   */
  public function getSize(): ?int {
    return null;
  }

  /**
   * Returns the current position of the file read/write pointer
   *
   * @return int Position of the file pointer
   * @throws \RuntimeException on error.
   */
  public function tell(): int {
    if (!is_resource($this->stream)) {
      throw new \RuntimeException(
        'Cannot determine position of detached stream.',
      );
    }

    return ftell($this->stream);
  }

  /**
   * Returns true if the stream is at the end of the stream.
   *
   * @return bool
   */
  public function eof(): bool {
    if (!is_resource($this->stream)) {
      return true;
    }

    return feof($this->stream);
  }

  /**
   * Returns whether or not the stream is seekable.
   *
   * @return bool
   */
  public function isSeekable(): bool {
    if (!is_resource($this->stream)) {
      return false;
    }
    return $this->seekable;
  }

  /**
   * Seek to a position in the stream.
   *
   * @link http://www.php.net/manual/en/function.fseek.php
   * @param int $offset Stream offset
   * @param int $whence Specifies how the cursor position will be calculated
   *     based on the seek offset. Valid values are identical to the built-in
   *     PHP $whence values for `fseek()`.  SEEK_SET: Set position equal to
   *     offset bytes SEEK_CUR: Set position to current location plus offset
   *     SEEK_END: Set position to end-of-stream plus offset.
   * @throws \RuntimeException on failure.
   */
  public function seek(int $offset, Whence $whence = Whence::SET): void {
    if ($offset === null) {
      throw new \RuntimeException('Null offset');
    }

    if (!is_resource($this->stream)) {
      throw new \RuntimeException('Stream detached from message.');
    }

    if (!$this->seekable) {
      throw new \RuntimeException('Stream is not seekable.');
    }

    fseek($this->stream, $offset, $whence);
  }

  /**
   * Seek to the beginning of the stream.
   *
   * If the stream is not seekable, this method will raise an exception;
   * otherwise, it will perform a seek(0).
   *
   * @see seek()
   * @link http://www.php.net/manual/en/function.fseek.php
   * @throws \RuntimeException on failure.
   */
  public function rewind(): void {
    $this->seek(0);
  }

  /**
   * Returns whether or not the stream is writable.
   *
   * @return bool
   */
  public function isWritable(): bool {
    if (!is_resource($this->stream)) {
      return false;
    }
    return $this->writable;
  }

  /**
   * Write data to the stream.
   *
   * @param string $string The string that is to be written.
   * @return int Returns the number of bytes written to the stream.
   * @throws \RuntimeException on failure.
   */
  public function write(string $string): int {
    if ($string === null) {
      $string = '';
    }

    if (!is_resource($this->stream)) {
      throw new \RuntimeException('Attempted to write to a detached stream.');
    }

    if (!$this->writable) {
      throw new \RuntimeException(
        'Attempted to write to a read-only stream.',
      );
    }

    $result = fwrite($this->stream, $string);

    if ($result === false) {
      throw new \RuntimeException('Error while writing to stream.');
    }

    return $result;
  }

  /**
   * Returns whether or not the stream is readable.
   *
   * @return bool
   */
  public function isReadable(): bool {
    return $this->readable;
  }

  /**
   * Read data from the stream.
   *
   * @param int $length Read up to $length bytes from the object and return
   *     them. Fewer than $length bytes may be returned if underlying stream
   *     call returns fewer bytes.
   * @return string Returns the data read from the stream, or an empty string
   *     if no bytes are available.
   * @throws \RuntimeException if an error occurs.
   */
  public function read(int $length): string {
    if ($length === null) {
      return '';
    }

    if (!is_resource($this->stream)) {
      throw new \RuntimeException(
        'Attempted to read from a detached stream.',
      );
    }

    if (!$this->writable) {
      throw new \RuntimeException(
        'Attempted to read from a write-only stream.',
      );
    }

    if (feof($this->stream)) {
      return '';
    }

    $result = fread($this->stream, $length);

    if ($result === false) {
      throw new \RuntimeException('Error while reading from stream.');
    }

    return $result;
  }

  /**
   * Returns the remaining contents in a string
   *
   * @return string
   * @throws \RuntimeException if unable to read or an error occurs while
   *     reading.
   */
  public function getContents(): string {
    if (!is_resource($this->stream)) {
      throw new \RuntimeException(
        'Attempted to read from a detached stream.',
      );
    }

    $result = stream_get_contents($this->stream);
    if ($result === false) {
      throw new \RuntimeException('Error while reading from stream.');
    }

    return $result;
  }

  private function setReadWrite(string $mode): void {
    if (substr($mode, -1) === '+') {
      $this->readable = true;
      $this->writable = true;
      return;
    }

    if ($mode === 'r') {
      $this->readable = true;
      return;
    }

    $writableModes = Set {'w', 'a', 'x', 'c'};
    if ($writableModes->contains($mode)) {
      $this->writable = true;
      return;
    }

  }

  public function __destruct() {
    $this->close();
  }
}
