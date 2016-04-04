<?hh // strict

namespace HackPack\HackMini\Test\Command;

use HackPack\HackUnit\Contract\Assert;
use HackPack\HackMini\Command\UserInteraction;
use HackPack\HackMini\Command\StringReader;
use HackPack\HackMini\Command\StringWriter;

class UserInteractionTest {

  private StringReader $reader;
  private StringWriter $writer;

  public function __construct() {
    $this->reader = new StringReader();
    $this->writer = new StringWriter();
  }

  <<Setup>>
  public function clearIO(): void {
    $this->reader->set('');
    $this->writer->clear();
  }

  private function buildInteraction(): UserInteraction {
    return new UserInteraction($this->reader, $this->writer);
  }

  <<Test>>
  public function questionIsRendered(Assert $assert): void {
    $interaction = $this->buildInteraction();
    $this->reader->addLine('The answer');

    $answer = $interaction->ask('This is the question.');
    $assert->string($this->writer->mostRecent())->is('This is the question.');
    $assert->string($answer)->is('The answer');
  }

  <<Test>>
  public function wrongSelection(Assert $assert) : void {
    $interaction = $this->buildInteraction();
    $this->reader->addLine('wrong');
    $this->reader->addLine('wrong');

    $answer = $interaction->select('Q', Set{'right', 'not wrong'}, 2);
    $assert->mixed($answer)->isNull();
  }

  <<Test>>
  public function rightSelection(Assert $assert) : void {
    $interaction = $this->buildInteraction();
    $this->reader->addLine('wrong');
    $this->reader->addLine('right');

    $answer = $interaction->select('Q', Set{'right', 'not wrong'}, 2);
    $assert->mixed($answer)->identicalTo('right');
  }
}
