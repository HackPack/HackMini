<?hh // strict

namespace HackPack\HackMini\Container;

type ParsedDefinition = shape(
    'name' => string,
    'return' => string,
    'function' => ?string,
    'class' => ?string,
    'method' => ?string,
);
