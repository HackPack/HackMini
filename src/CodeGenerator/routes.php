<?hh // strict

namespace HackPack\HackMini\CodeGenerator;

use FactoryContainer;
use HackPack\HackMini\Command\Request;
use HackPack\HackMini\Command\UserInteraction;

<<Command('routes:generate')>>
function generateRoutesHandler(
  FactoryContainer $c,
  Request $req,
  UserInteraction $interaction,
): int {
  // Your code here
  $interaction->showLine('Not implemented');
  return 1;
}
