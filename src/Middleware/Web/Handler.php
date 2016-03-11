<?hh // strict

namespace HackPack\HackMini\Middleware\Web;

use FactoryContainer;
use HackPack\HackMini\Message\Request;
use HackPack\HackMini\Message\Response;

type Handler = (function(FactoryContainer, Request, Response) : Response);
