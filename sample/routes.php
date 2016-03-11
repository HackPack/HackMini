<?hh // strict

use HackPack\HackMini\Middleware\Web\Handler;
use HackPack\HackMini\Message\RestMethod;

function routes() : Map<RestMethod, Map<string, Handler>>
{
    return Map{
        RestMethod::Post => Map{
            '/user' => fun('HackPack\HackMini\Sample\createUserFromWeb'),
        },
        RestMethod::Get => Map{
            '/user/(\d+)' => fun('HackPack\HackMini\Sample\showUser'),
            '/me' => fun('HackPack\HackMini\Sample\showMyProfile')
        },
    };
}
