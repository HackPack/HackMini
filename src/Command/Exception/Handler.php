<?hh // strict

namespace HackPack\HackMini\Command\Exception;

use HackPack\HackMini\Command\UserInteraction;
use HackPack\HackMini\Command\Exception\MissingInput;
use HackPack\HackMini\Util;

class Handler {
  public function __construct(private UserInteraction $interaction) {}

  public function handle(\Exception $e): int {
    if($e instanceof \HackPack\HackMini\Command\Exception\MissingInput) {
      $this->renderMissingInput($e);
      return 1;
    }

    $this->interaction->showLine(sprintf(
      'Uncaught exception of type %s with message "%s"',
      get_class($e),
      $e->getMessage()
    ));
    return 1;
  }

  private function renderMissingInput(MissingInput $exception): void {
    $this->interaction->showLine('');
    $this->interaction->showLine(sprintf(
      'Required input missing: %s',
      $exception->inputName()
    ));
    $this->interaction->showLine('');

    $name = $exception->request()->name();
    $commandDefinition = \commands()->at($name);
    Util\displayCommandHelp($name, $commandDefinition, $this->interaction);
  }
}
