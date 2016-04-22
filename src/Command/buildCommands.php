<?hh // strict

namespace HackPack\HackMini\Command;

use HackPack\HackMini\Command\Request;
use HackPack\HackMini\Command\UserInteraction;
use HackPack\HackMini\Middleware\DefinitionParser as MiddlewareParser;
use FredEmmott\DefinitionFinder\FileParser;
use HackPack\HackMini\Util;

<<Command('commands:build'), Options('i|include-path=', 'e|exclude-path=')>>
function buildCommandsHandler(
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
    $functions = Vector {};
    $classes = Vector {};

    foreach ($fileList as $finfo) {
      if ($finfo->isFile() && $finfo->isReadable()) {
        $fileParser = FileParser::FromFile($finfo->getRealPath());
        $functions->addAll($fileParser->getFunctions());
        $classes->addAll($fileParser->getClasses());
      }
    }

  $middlewareParser = new MiddlewareParser($functions, $classes);
  if ($middlewareParser->failures()) {
    var_dump($middlewareParser->failures());
    return 1;
  }

  $commandParser = new DefinitionParser($functions, $classes);
  if ($commandParser->failures()) {
    var_dump($commandParser->failures());
    return 1;
  }

  $builder =
    new Builder($commandParser->commands(), $middlewareParser->middleware());

  Util\recursiveMakeDir(dirname($outfile));
  $fp = fopen($outfile, 'w');
  fwrite($fp, $builder->render());
  fclose($fp);
  return 0;
}
