<?hh // strict

use HackPack\HackMini\Contract\Middleware;
use HackPack\HackMini\Command\Request as CliRequest;
use HackPack\HackMini\Command\UserInteraction;
use HackPack\HackMini\Message\Request as WebRequest;
use HackPack\HackMini\Message\Response;

function globalWebMiddleware() : Vector<
    Middleware<WebRequest,Response,Response>
>
{
    return Vector{};
}

function globalCliMiddleware() : Vector<
    Middleware<CliRequest,UserInteraction,int>
>
{
    return Vector{};
}
