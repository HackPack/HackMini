<?hh // strict

namespace HackPack\HackMini\Command;

use HackPack\HackMini\Contract\Command\Writer;

class StringWriter implements Writer {
  private Vector<string> $stack = Vector {};

  public function write(string $data): void {
    $this->stack->add($data);
  }

  public function clear(): void {
    $this->stack->clear();
  }

  public function mostRecent(): string {
    if ($this->stack->isEmpty()) {
      return '';
    }
    return $this->stack->at($this->stack->count() - 1);
  }

  public function count(): int {
    return $this->stack->count();
  }

  public function get(int $offset): ?string {
    return $this->stack->get($offset);
  }

  public function at(int $offset): string {
    return $this->stack->at($offset);
  }
}
