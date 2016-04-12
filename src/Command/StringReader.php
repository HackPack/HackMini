<?hh // strict

namespace HackPack\HackMini\Command;

use HackPack\HackMini\Contract\Command\Reader;

class StringReader implements Reader {
  private int $position = 0;
  private string $data = '';

  public function read(int $bytes): string {
    $result = substr($this->data, $this->position, $bytes);
    if (!is_string($result)) {
      return '';
    }
    $this->movePosition(strlen($result));
    return $result;
  }

  public function readline(): string {
    $nextNewline = strpos($this->data, PHP_EOL, $this->position);

    // No newline found, so return from position to end of buffer
    if (!is_int($nextNewline)) {
      return $this->read(strlen($this->data));
    }
    // Do not include the trailing newline
    return rtrim($this->read($nextNewline - $this->position + 1), PHP_EOL);
  }

  public function set(string $data): void {
    $this->data = $data;
    $this->position = 0;
  }

  public function append(string $data): void {
    $this->data .= $data;
  }

  public function addLine(string $line): void {
    $this->append($line.PHP_EOL);
  }

  /**
   * Keep the position between 0 and the last byte of the string.
   */
  private function movePosition(int $delta): void {
    $this->position += $delta;
    $this->position = min(strlen($this->data) - 1, $this->position);
    $this->position = max(0, $this->position);
  }
}
