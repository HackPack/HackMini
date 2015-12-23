<?hh // strict

namespace HackPack\HackMini\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

interface Handler
{
    public function handle(Request $req, Response $rsp, (function(Request,Response):Response) $next): Response;
}
