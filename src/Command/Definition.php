<?hh // strict

namespace HackPack\HackMini\Command;

type Definition = shape(
    'arguments' => Vector<ArgumentDefinition>,
    'options' => Vector<OptionDefinition>,
    'handler' => Handler,
);
