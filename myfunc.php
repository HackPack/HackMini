<?hh // strict
;
<<
Command('myfunc')
// ,Arguments()
// ,Options()
>>
function myfuncHandler(
  \FactoryContainer $c,
  \HackPack\HackMini\Command\Request $req,
  \HackPack\HackMini\Command\UserInteraction $interaction,
): int {
  // Your code here
  $interaction->showLine('Not implemented');
  return 1;
}