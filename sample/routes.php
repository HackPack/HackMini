<?hh // strict

use HackPack\HackMini\Contract\Middleware;
use HackPack\HackMini\Message\Request;
use HackPack\HackMini\Message\Response;
use HackPack\HackMini\Message\RestMethod;
use HackPack\HackMini\Middleware\Web\Handler;

function routes(
): Map<RestMethod,
Map<string,
shape(
  'handler' => Handler,
  'middleware' => Vector<Middleware<Request, Response, Response>>,
)>> {
  return Map {
    RestMethod::Post => Map {
      '/user' => shape(
        'handler' => fun('HackPack\HackMini\Sample\createUserFromWeb'),
        'middleware' => Vector {},
      ),
    },
    RestMethod::Get => Map {
      '/user/(\d+)' => shape(
        'handler' => fun('HackPack\HackMini\Sample\showUser'),
        'middleware' => Vector {},
      ),
      '/me' => shape(
        'handler' => fun('HackPack\HackMini\Sample\showMyProfile'),
        'middleware' => Vector {},
      ),
    },
  };
}
