<?hh

namespace HackPack\HackMini\Message;

<<Provides('ServerRequest')>>
function buildRequestFromGlobals(
  \FactoryContainer $c,
): \HackPack\HackMini\Message\Request {
  $server = new Map($_SERVER);

  // Determine the http protocol version
  $protocol = HttpProtocolVersion::coerce($server->get('SERVER_PROTOCOL'));
  $protocol = $protocol === null ? HttpProtocolVersion::v10 : $protocol;

  $method =
    $server->containsKey('REQUEST_METHOD')
      ? $server->at('REQUEST_METHOD')
      : '';
  $method = RestMethod::coerce($method);
  if ($method === null) {
    $method = RestMethod::Unknown;
  }

  $path =
    $server->containsKey('REQUEST_URI') ? $server->at('REQUEST_URI') : '/';
  $scriptUri = $server->containsKey('SCRIPT_URI') ? $server->at('SCRIPT_URI'): '';

  $uri = Uri::fromString($scriptUri)->withPath($path);

  return new Request(
    $protocol,
    $method,
    $uri,
    new Map(getallheaders()),
    $_COOKIE,
    $c->getServerBody(),
  );
}

<<Provides('ServerBody')>>
function buildServerBodyFromGlobals(
  \FactoryContainer $c,
): \HackPack\HackMini\Message\StreamBody {
  return new StreamBody(fopen('php://input', 'r'));
}
