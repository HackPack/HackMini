<?hh // strict

namespace HackPack\HackMini\Command;

use HackPack\HackMini\Command\Request;
use HackPack\HackMini\Command\UserInteraction;

use HackPack\HackMini\Util;

<<Command('commands:build'), Options('i|include-path=', 'e|exclude-path=')>>
function buildCommandsCommand(
    \FactoryContainer $c,
    \HackPack\HackMini\Command\Request $req,
    \HackPack\HackMini\Command\UserInteraction $rsp,
) : int
{
    $fileList = Util\listPhpFiles(
        $req->get('include-path'),
        $req->get('exclude-path'),
    );
    return buildCommands($fileList,  $req->projectRoot() . '/commands.php');
}

function buildCommands(Vector<\SplFileInfo> $fileList, string $outfile) : int
{
    $parser = DefinitionParser::fromFileList($fileList);
    if($parser->failures()) {
        var_dump($parser->failures());
        return 1;
    }
    $builder = new Builder($parser->commands());
    $fp = fopen($outfile, 'w');
    fwrite($fp, $builder->render());
    fclose($fp);
    return 0;
}
