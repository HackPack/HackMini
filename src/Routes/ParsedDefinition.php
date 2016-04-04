<?hh // strict

namespace HackPack\HackMini\Routes;

use HackPack\HackMini\Message\RestMethod;

type ParsedDefinition = shape(
  'verb' => RestMethod,
  'pattern' => string,
  'middleware' => \ConstVector<string>,
  'function' => ?string,
  'class' => ?string,
  'method' => ?string,
);
