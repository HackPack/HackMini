<?hh // strict

namespace HackPack\HackMini\Routes;

use HackPack\HackMini\Command\Request;
use HackPack\HackMini\Command\UserInteraction;
use HackPack\HackMini\Middleware\DefinitionParser as MiddlewareParser;
use HackPack\HackMini\Routes\Builder;
use HackPack\HackMini\Util;
use FredEmmott\DefinitionFinder\FileParser;

<<Command('routes:build'), Options('i|include-path=', 'e|exclude-path=')>>
function buildRoutesHandler(
  \FactoryContainer $c,
  \HackPack\HackMini\Command\Request $req,
  \HackPack\HackMini\Command\UserInteraction $interaction,
): int {
  $outfile = $req->projectRoot().'/build/routes.php';
  $fileList =
    Util\listPhpFiles($req->get('include-path'), $req->get('exclude-path'));

  return buildRoutes($fileList, $outfile);
}

function buildRoutes(Vector<\SplFileInfo> $fileList, string $outfile): int {
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
    new Builder($commandParser->routes(), $middlewareParser->middleware());

  Util\recursiveMakeDir(dirname($outfile));
  $fp = fopen($outfile, 'w');
  fwrite($fp, $builder->render());
  fclose($fp);
  return 0;
}
