<?hh // strict

use HackPack\HackMini\Contract\MiddlewareFactory;
use HackPack\HackMini\Message\Request;
use HackPack\HackMini\Message\Response;
use HackPack\HackMini\Message\RestMethod;
use HackPack\HackMini\Middleware\Web\Handler;

function routes(
): Map<RestMethod,
Map<string,
shape(
  'handler' => Handler,
  'middleware' => Vector<MiddlewareFactory<Request, Response, Response>>,
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
        'middleware' => Vector {
          class_meth('HackPack\HackMini\Sample\RequireUser', 'factory'),
        },
      ),
      '/me' => shape(
        'handler' => fun('HackPack\HackMini\Sample\showMyProfile'),
        'middleware' => Vector {
          class_meth('HackPack\HackMini\Sample\RequireUser', 'factory'),
        },
      ),
    },
  };
}