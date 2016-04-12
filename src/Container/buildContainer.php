<?hh // strict

namespace HackPack\HackMini\Container;

use HackPack\HackMini\Command\Request;
use HackPack\HackMini\Command\UserInteraction;

use HackPack\HackMini\Util;

<<Command('container:build'), Options('i|include-path=', 'e|exclude-path=')>>
function buildContainerCommand(
  \FactoryContainer $c,
  \HackPack\HackMini\Command\Request $req,
  \HackPack\HackMini\Command\UserInteraction $rsp,
): int {
  $fileList =
    Util\listPhpFiles($req->get('include-path'), $req->get('exclude-path'));
  return buildContainer(
    $fileList,
    $req->projectRoot().'/build/FactoryContainer.php',
  );
}

function buildContainer(Vector<\SplFileInfo> $fileList, string $outfile): int {
  $parser = DefinitionParser::fromFileList($fileList);
  if ($parser->failures()) {
    var_dump($parser->failures());
    return 1;
  }
  $builder = new Builder($parser->services());
  $fp = fopen($outfile, 'w');
  fwrite($fp, $builder->render());
  fclose($fp);
  return 0;
}
