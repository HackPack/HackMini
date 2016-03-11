<?hh // strict

namespace HackPack\HackMini\Command;

type Definition = shape(
    'arguments' => Vector<string>,
    'options' => Vector<OptionDefinition>,
    'handler' => Handler,
);
