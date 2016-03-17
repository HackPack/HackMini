<?hh // strict

namespace HackPack\HackMini\Command;

use HackPack\HackMini\Command\Request;
use HackPack\HackMini\Command\UserInteraction;

use function HackPack\HackMini\Util\listAllFiles;

<<Command('commands:build'), Options('i|include-path=', 'e|exclude-path=')>>
function buildCommandsCommand(\FactoryContainer $c, Request $req, UserInteraction $rsp) : void
{
    $fileList = listAllFiles(
        $req->get('include-path'),
        $req->get('exclude-path'),
    );
    $parser = DefinitionParser::fromFileList($fileList);
    $builder = new Builder($parser->commands());
    file_put_contents($builder->render(), $req->projectRoot() . '/commands.php');
}
