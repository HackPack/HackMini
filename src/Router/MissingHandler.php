<?hh // strict

namespace HackPack\HackMini\Router;

use HackPack\HackMini\Message\Request;

class MissingWebHandler extends \Exception {
  public function __construct(public Request $req) {
    parent::__construct();
  }
}
