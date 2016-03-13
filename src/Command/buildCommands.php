<?hh // strict

namespace HackPack\HackMini\Command;

use HackPack\HackMini\Command\Request;
use HackPack\HackMini\Command\UserInteraction;

use function HackPack\HackMini\Util\listAllFiles;

<<Command('commands:build'), Options('i|include-path=', 'e|exclude-path=')>>
function buildCommandsCommand(\FactoryContainer $c, Request $req, UserInteraction $rsp) : void
{
    buildCommands($req->get('include-path'), $req->get('exclude-path'), $req->projectRoot() . '/commands.php');
}

function buildCommands(?Vector<string> $includes, ?Vector<string> $excludes, string $outpath) : void
{
    $fileList = listAllFiles($includes, $excludes);
    $parser = DefinitionParser::fromFileList($fileList);
    $builder = new Builder($parser->commands());
    file_put_contents($builder->render(), $outpath);
}
