<?hh // strict

<<Command('myclass') /*, Arguments(), Options()*/ >>
function myclassHandler(
  \FactoryContainer $c,
  \HackPack\HackMini\Command\Request $req,
  \HackPack\HackMini\Command\UserInteraction $interaction,
): int {
  // Your code here
  $interaction->showLine('Not implemented');
  return 1;
}