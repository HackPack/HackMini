<?hh // strict

namespace HackPack\HackMini\Router;

use HackPack\HackMini\Command\Request;
use HackPack\HackMini\Command\UserInteraction;

use function HackPack\HackMini\Util\listAllFiles;

<<Command('router:build'), Options('i|include-path=', 'e|exclude-path=')>>
function buildRouter(\FactoryContainer $c, Request $req, UserInteraction $rsp) : void
{
    $fileList = listAllFiles($req->get('include-path'), $req->get('exclude-path'));
}
