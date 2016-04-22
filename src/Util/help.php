<?hh // strict

namespace HackPack\HackMini\Util;

use HackPack\HackMini\Command\Definition;
use HackPack\HackMini\Command\Request;
use HackPack\HackMini\Command\UserInteraction;

<<Command('help'), Arguments('command')>>
function helpHandler(
  \FactoryContainer $c,
  \HackPack\HackMini\Command\Request $req,
  \HackPack\HackMini\Command\UserInteraction $rsp,
): int {
  $commands = \commands();

  $name = $req->getFirst('command');

  if($name === null) {
    displayCommandList($commands->keys(), $rsp);
    return 0;
  }

  $definition = $commands->get($name);

  if($definition === null) {
    $rsp->showLine('Unknown command ' . $name);
    displayCommandList($commands->keys(), $rsp);
    return 1;
  }

  displayCommandHelp($name, $definition, $rsp);
  return 0;
}

function displayCommandList(Vector<string> $names, UserInteraction $rsp): void {
  $rsp->showLine('Available commands:');
  foreach($names as $name) {
    $rsp->showLine($name);
  }
}

function displayCommandHelp(string $name, Definition $definition, UserInteraction $rsp): void {
  $usage = implode(' ', (Vector{$name})->addAll($definition['arguments']->map($a ==> '<' . $a['name'] . '>')));
  $rsp->showLine($usage);

  $renderArgOrOption = $in ==> {
    $rsp->show($in['name'] . ': ');
    $in['description'] === '' ?
      $rsp->showLine('<no description>'):
      $rsp->showLine($in['description']);
  };
  if($definition['arguments']->count() > 0) {
    $rsp->showLine('');
    $rsp->showLine('Arguments:');
    $definition['arguments']->map($renderArgOrOption);
  }

  if($definition['options']->count() > 0) {
    $rsp->showLine('');
    $rsp->showLine('Options:');
    $definition['options']->map($renderArgOrOption);
  }
}
