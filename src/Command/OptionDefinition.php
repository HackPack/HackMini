<?hh // strict

namespace HackPack\HackMini\Command;

type OptionDefinition = shape(
    'name' => string,
    'alias' => ?string,
    'value required' => bool,
);
