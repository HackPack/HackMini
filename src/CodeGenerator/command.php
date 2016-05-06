<?hh // strict

namespace HackPack\HackMini\CodeGenerator;

use FactoryContainer;
use HackPack\HackMini\Command\Request;
use HackPack\HackMini\Command\UserInteraction;

use HackPack\HackMini\Util;

<<
Command('commands:generate'),
Arguments('name'),
Options('p|path=', 'c|class')
>>
function generateCommandHandler(
  FactoryContainer $c,
  Request $req,
  UserInteraction $interaction,
): int {
  $name = $req->atFirst('name');
  $path = $req->getFirst('path');
  if ($path === null) {
    $path =
      $interaction->ask(
        sprintf(
          'In which directory would you like to put the new command handler? (relative to %s)',
          getcwd(),
        ),
      );
  }
  if ($path === '') {
    $path = getcwd();
  }

  if (is_file($path)) {
    $interaction->showLine(
      'The path you selected already exists as a regular file.',
    );
    return 1;
  }
  if (!Util\recursiveMakeDir($path)) {
    $interaction->showLine(
      'Unable to create the directory to contain the new command Handler.',
    );
  }

  $parts = explode('\\', $name);
  $name = array_pop($parts);
  $namespace =
    count($parts) > 0
      ? PHP_EOL.'namespace '.implode('\\', $parts).';'.PHP_EOL
      : '';

  $generateClass = $req->has('class');

  $code =
    $generateClass
      ? methodCommandHandler($name, $namespace)
      : functionCommandHandler($name, $namespace);

  if ($generateClass) {
    $name = ucfirst($name);
  }

  $filename = Util\implodePath(Vector {$path, $name.'.php'});
  $interaction->showLine('Writing new command handler to '.$filename);
  file_put_contents($filename, $code);

  return 0;
}

function functionCommandHandler(string $name, string $namespace): string {

  return<<<Hack
<?hh // strict

{$namespace}

use FactoryContainer;
use HackPack\\HackMini\\Command\\Request;
use HackPack\\HackMini\\Command\\UserInteraction;

<<Command('{$name}')>>
function {$name}Handler(
  FactoryContainer \$c,
  Request \$req,
  UserInteraction \$interaction,
): int {
  // Your code here
  \$interaction->showLine('Not implemented');
  return 1;
}
Hack;

}

function methodCommandHandler(string $name, string $namespace): string {
  $ucName = ucfirst($name);

  return<<<Hack
<?hh // strict

{$namespace}

use FactoryContainer;
use HackPack\\HackMini\\Command\\Request;
use HackPack\\HackMini\\Command\\UserInteraction;
class {$ucName} {
  <<Command('{$name}')>>
  public static function handle(
    FactoryContainer \$c,
    Request \$req,
    UserInteraction \$interaction,
  ): int {
    // Your code here
    \$interaction->showLine('Not implemented');
    return 1;
  }
}
Hack;
}
