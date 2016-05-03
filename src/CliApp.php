<?hh // strict

namespace HackPack\HackMini;

require_once __DIR__.'/Command/buildCommands.php';

class CliApp {
  public function __construct(
    private Command\Request $request,
    private Command\UserInteraction $interaction,
  ) {
    $generateAutoload = false;

    if (!function_exists('commands')) {
      $this->buildCommands();
      $generateAutoload = true;
    }

    if (!function_exists('routes')) {
      $this->buildRoutes();
      $generateAutoload = true;
    }

    if (!function_exists('globalCliMiddleware')) {
      $this->buildGlobalMiddleware();
      $generateAutoload = true;
    }

    if (!class_exists('FactoryContainer')) {
      $this->buildFactoryContainer();
      $generateAutoload = true;
    }

    if ($generateAutoload) {
      $composer = $this->findComposer();
      system('hhvm '.$composer.' dumpautoload');
    }
  }

  public function run(): int {
    $router = new Router\Command(
      new \FactoryContainer(),
      \commands(),
      \globalCliMiddleware(),
    );

    try {
      return $router->dispatch($this->request, $this->interaction);
    } catch (\Exception $e) {
      $handler = new Command\Exception\Handler($this->interaction);
      return $handler->handle($e);
    }
  }

  private function buildCommands(): void {
    $this->interaction->showLine('Scanning project for commands.');
    $outfile = $this->request->projectRoot().'/build/commands.php';
    $dirsToScan = $this->request->projectRoot();
    $filesToScan = Util\listPhpFiles(Vector {$dirsToScan}, null);
    if (\HackPack\HackMini\Command\buildCommands($filesToScan, $outfile)) {
      exit(1);
    }

    /* HH_IGNORE_ERROR[1002] */
    require_once $outfile;
  }

  private function buildRoutes(): void {
    $this->interaction->showLine('Scanning project for routes.');
    $outfile = $this->request->projectRoot().'/build/routes.php';
    $dirsToScan = $this->request->projectRoot();
    $filesToScan = Util\listPhpFiles(Vector {$dirsToScan}, null);
    if (Routes\buildRoutes($filesToScan, $outfile)) {
      exit(1);
    }

    /* HH_IGNORE_ERROR[1002] */
    require_once $outfile;
  }

  private function buildGlobalMiddleware(): void {
    $code = <<<Hack
<?hh // strict

use HackPack\HackMini\Contract\Middleware;
use HackPack\HackMini\Command\Request as CliRequest;
use HackPack\HackMini\Command\UserInteraction;
use HackPack\HackMini\Message\Request as WebRequest;
use HackPack\HackMini\Message\Response;

function globalWebMiddleware(
): Vector<Middleware<WebRequest, Response, Response>> {
  return Vector {};
}

function globalCliMiddleware(
): Vector<Middleware<CliRequest, UserInteraction, int>> {
  return Vector {};
}
Hack;
    $outfile = $this->request->projectRoot().'/middleware.php';
    $bytesWritten = file_put_contents($outfile, $code);
    if ($bytesWritten === false) {
      $this->interaction->showLine('Unable to write global middleware file.');
      exit(1);
    }

    require_once ($outfile);
  }

  private function buildFactoryContainer(): void {
    $this->interaction->showLine('Scanning project for service factories.');
    $outfile = $this->request->projectRoot().'/build/FactoryContainer.php';
    $dirsToScan = $this->request->projectRoot();
    $filesToScan = Util\listPhpFiles(Vector {$dirsToScan}, null);
    if (\HackPack\HackMini\Container\buildContainer($filesToScan, $outfile)) {
      exit(1);
    }

    /* HH_IGNORE_ERROR[1002] */
    require_once $outfile;
  }

  private function findComposer(): string {
    $paths = [
      $this->request->projectRoot().'/composer.phar',
      exec('which composer'),
    ];

    foreach ($paths as $path) {
      if (is_file($path)) {
        return $path;
      }
    }

    $this->interaction->showLine('Unable to locate composer.');
    exit(1);
  }
}
