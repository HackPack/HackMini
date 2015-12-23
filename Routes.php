<?hh // strict

namespace HackPack\HackMini;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

function routes() : Map<HttpVerb,Map<string,(function(Request,Response,FactoryContainer):Response)>>
{
    return Map{};
}
