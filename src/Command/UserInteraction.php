<?hh // strict

namespace HackPack\HackMini\Command;

use HackPack\HackMini\Contract\Command\Reader;
use HackPack\HackMini\Contract\Command\Writer;

<<__ConsistentConstruct>>
class UserInteraction {
  public function __construct(private Reader $reader, private Writer $writer) {}

  public function show(string $message ): void {
    $this->writer->write($message);
  }

  public function showLine(string $message): void {
    $this->writer->write($message.PHP_EOL);
  }

  public function showList(Traversable<string> $list): void {
    foreach ($list as $line) {
      $this->showLine($line);
    }
  }

  /**
   * Display a question to the user and read a single line of input.
   * Optionally specify a list of acceptable answers, and the maximum
   * number of attempts.  If the max attempts is negative, the user will be
   * asked the question until a selection from the list is given.
   */
  public function select(
    string $question,
    \ConstSet<string> $answers,
    int $maxAttempts = -1,
  ): ?string {
    if($answers->isEmpty()) {
    throw new \UnexpectedValueException('You must supply at least one selection.');
    }
    $answerList = '['.implode(', ', $answers).']';
    $question = $question . ' ' . $answerList . PHP_EOL . '>';

    $attempts = 0;
    while ($maxAttempts < 0 || $attempts < $maxAttempts) {

      $result = $this->ask($question);
      if ($answers->isEmpty() || $answers->contains($result)) {
        return $result;
      }

      $attempts++;
      $this->showLine('Please select from the list shown.');

      if ($maxAttempts > -1 && $attempts < $maxAttempts) {
        $this->show(sprintf(
          'You have %d attempt(s) left.',
          $maxAttempts - $attempts,
        ));
      }
    }
    return null;
  }

  public function ask(string $question):string
  {
    $this->show($question);
    return $this->reader->readline();
  }
}
