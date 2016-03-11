<?hh // strict

namespace HackPack\HackMini\Middleware;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

type Next<Trequest, Tresponse> = (function(Trequest,Tresponse):Tresponse);
