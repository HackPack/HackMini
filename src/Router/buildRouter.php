<?hh // strict

namespace HackPack\HackMini\Router;

use HackPack\HackMini\Command\Request;
use HackPack\HackMini\Command\UserInteraction;

use HackPack\HackMini\Util;

<<Command('routes:build'), Options('i|include-path=', 'e|exclude-path=')>>
function buildRoutes(
    \FactoryContainer $c,
    \HackPack\HackMini\Command\Request $req,
    \HackPack\HackMini\Command\UserInteraction $interaction,
) : int
{
    $fileList = Util\listPhpFiles($req->get('include-path'), $req->get('exclude-path'));
    return 1;
}
