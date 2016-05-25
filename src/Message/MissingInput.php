<?hh // strict

namespace HackPack\HackMini\Message;

use HackPack\HackMini\Validator\Validator;

final class InvalidInput extends \Exception {
  public function __construct(string $message) {
    parent::__construct($message);
  }
}
