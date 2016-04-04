<?hh // strict

namespace HackPack\HackMini\Routes;

use HackPack\HackMini\Command\Request;
use HackPack\HackMini\Command\UserInteraction;
use HackPack\HackMini\Middleware\DefinitionParser as MiddlewareParser;
use HackPack\HackMini\Routes\Builder;

use HackPack\HackMini\Util;

<<Command('routes:build'), Options('i|include-path=', 'e|exclude-path=')>>
function buildRoutes(
    \FactoryContainer $c,
    \HackPack\HackMini\Command\Request $req,
    \HackPack\HackMini\Command\UserInteraction $interaction,
) : int
{
    $outfile = $req->projectRoot() . '/build/routes.php';
    $fileList = Util\listPhpFiles($req->get('include-path'), $req->get('exclude-path'));

    $middlewareParser = MiddlewareParser::fromFileList($fileList);
    if($middlewareParser->failures()) {
        var_dump($middlewareParser->failures());
        return 1;
    }

    $routeParser = DefinitionParser::fromFileList($fileList);
    if($routeParser->failures()) {
        var_dump($routeParser->failures());
        return 1;
    }

    $builder = new Builder(
        $routeParser->routes(),
        $middlewareParser->middleware(),
    );

    $fp = fopen($outfile, 'w');
    fwrite($fp, $builder->render());
    fclose($fp);

    return 0;
}
