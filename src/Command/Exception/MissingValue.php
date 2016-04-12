<?hh // strict

namespace HackPack\HackMini\Command\Exception;

use HackPack\HackMini\Command\Request;

class MissingValue extends \Exception {
  public function __construct(private string $inputName) {
    parent::__construct();
  }

  public function inputName(): string {
    return $this->inputName;
  }
}
