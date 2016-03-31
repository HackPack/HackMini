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

  $uri = $server->containsKey('SCRIPT_URI') ? $server->at('SCRIPT_URI') : '/';

  $headerKeys = Map {};
  $headerValues = Map {};
  foreach (getallheaders() as $key => $line) {
    $lowerKey = strtolower($key);
    $headerKeys->set($lowerKey, $key);
    $headerValues->set($lowerKey, new Vector(explode(',', $line)));
  }

  return new Request(
    $protocol,
    $method,
    Uri::fromString($uri),
    $headerValues,
    $headerKeys,
    $c->getServerBody(),
  );
}

<<Provides('ServerBody')>>
function buildServerBodyFromGlobals(
  \FactoryContainer $c,
): \HackPack\HackMini\Message\StreamBody {
  return new StreamBody(fopen('php://input', 'r'));
}
