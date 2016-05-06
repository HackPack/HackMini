<?hh // strict

namespace HackPack\HackMini\Message;

use HackPack\HackMini\Contract\Message\Stream;

/**
 * SEEK_SET: Set position equal to offset bytes
 * SEEK_CUR: Set position to current location plus offset
 * SEEK_END: Set position to end-of-stream plus offset.
 */
enum Whence : int as int {
  SET = SEEK_SET;
  CUR = SEEK_CUR;
  END = SEEK_END;
}

class StringBody implements Stream {

  private int $position = 0;

  public function __construct(private string $message) {}

  public function __toString(): string {
    $this->rewind();
    return $this->getContents();
  }

  /**
   * Closes the stream and any underlying resources.
   *
   * @return void
   */
  public function close(): void {}

  /**
   * Get the size of the stream if known.
   *
   * @return int|null Returns the size in bytes if known, or null if unknown.
   */
  public function getSize(): ?int {
    return strlen($this->message);
  }

  /**
   * Returns the current position of the file read/write pointer
   *
   * @return int Position of the file pointer
   * @throws \RuntimeException on error.
   */
  public function tell(): int {
    return $this->position;
  }

  /**
   * Returns true if the stream is at the end of the stream.
   *
   * @return bool
   */
  public function eof(): bool {
    return $this->position >= strlen($this->message);
  }

  /**
   * Returns whether or not the stream is seekable.
   *
   * @return bool
   */
  public function isSeekable(): bool {
    return true;
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
    switch ($whence) {
      case Whence::SET:
        $this->position = $offset;
        return;
      case Whence::CUR:
        $this->position += $offset;
        return;
      case Whence::END:
        $this->position = strlen($this->message) + $offset;
        return;
    }
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
    $this->position = 0;
  }

  /**
   * Returns whether or not the stream is writable.
   *
   * @return bool
   */
  public function isWritable(): bool {
    return true;
  }

  /**
   * Write data to the stream.
   *
   * @param string $string The string that is to be written.
   * @return int Returns the number of bytes written to the stream.
   * @throws \RuntimeException on failure.
   */
  public function write(string $string): int {

    $written = strlen($string);

    $before = substr($this->message, 0, $this->position);
    $after = substr($this->message, $this->position + $written);

    $this->position = strlen($before) + $written - 1;
    $this->message = $before.$string.$after;

    return $written;
  }

  /**
   * Returns whether or not the stream is readable.
   *
   * @return bool
   */
  public function isReadable(): bool {
    return true;
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
    $result = substr($this->message, $this->position, $length);
    if ($result === false) {
      return '';
    }
    $this->position += strlen($result);
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
    $result = substr($this->message, $this->position);
    if ($result === false) {
      return '';
    }
    $this->position += strlen($result);
    return $result;
  }
}
