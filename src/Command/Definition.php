<?hh // strict

namespace HackPack\HackMini\Command;

use HackPack\HackMini\Contract\Middleware;
use FactoryContainer;

type Definition = shape(
  'arguments' => Vector<ArgumentDefinition>,
  'options' => Vector<OptionDefinition>,
  'handler' => Handler,
  'middleware' => Vector<(function(FactoryContainer): Middleware<Request,
  UserInteraction,
  int>)>,
);
