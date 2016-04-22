<?hh // strict

namespace HackPack\HackMini\CodeGenerator;

<<Command('routes:generate')>>
function generateRoutesHandler(
  \FactoryContainer $c,
  \HackPack\HackMini\Command\Request $req,
  \HackPack\HackMini\Command\UserInteraction $interaction,
): int {
  // Your code here
  $interaction->showLine('Not implemented');
  return 1;
}
