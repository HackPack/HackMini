<?hh // strict

namespace HackPack\HackMini\Command;

use HackPack\HackMini\Command\Request;
use HackPack\HackMini\Command\UserInteraction;
use HackPack\HackMini\Middleware\DefinitionParser as MiddlewareParser;

use HackPack\HackMini\Util;

<<Command('commands:build'), Options('i|include-path=', 'e|exclude-path=')>>
function buildCommandsCommand(
  \FactoryContainer $c,
  \HackPack\HackMini\Command\Request $req,
  \HackPack\HackMini\Command\UserInteraction $rsp,
): int {
  $fileList =
    Util\listPhpFiles($req->get('include-path'), $req->get('exclude-path'));
  $rsp->showLine('Building commands');
  return buildCommands($fileList, $req->projectRoot().'/build/commands.php');
}

function buildCommands(Vector<\SplFileInfo> $fileList, string $outfile): int {
  // TODO: optimize this.  Both are parsing the same files.
  $middlewareParser = MiddlewareParser::fromFileList($fileList);
  if ($middlewareParser->failures()) {
    var_dump($middlewareParser->failures());
    return 1;
  }

  $commandParser = DefinitionParser::fromFileList($fileList);
  if ($commandParser->failures()) {
    var_dump($commandParser->failures());
    return 1;
  }

  $builder =
    new Builder($commandParser->commands(), $middlewareParser->middleware());

  $fp = fopen($outfile, 'w');
  fwrite($fp, $builder->render());
  fclose($fp);
  return 0;
}
