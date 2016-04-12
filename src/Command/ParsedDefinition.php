<?hh // strict

namespace HackPack\HackMini\Command;

type ParsedDefinition = shape(
  'name' => string,
  'function' => ?string,
  'class' => ?string,
  'method' => ?string,
  'arguments' => \ConstVector<ArgumentDefinition>,
  'options' => \ConstVector<OptionDefinition>,
  'middleware' => \ConstVector<string>,
);
