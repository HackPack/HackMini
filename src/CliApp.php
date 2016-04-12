<?hh // strict

namespace HackPack\HackMini;

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

    if (!function_exists('globalCliMiddleware')) {
      $this->buildGlobalMiddleware();
      $generateAutoload = true;
    }

    if (!class_exists('FactoryContainer')) {
      $this->buildFactoryContainer();
      $generateAutoload = true;
    }

    if ($generateAutoload) {
      system('hhvm /usr/local/bin/composer dumpautoload');
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
    if (Command\buildCommands($filesToScan, $outfile)) {
      exit(1);
    }
    ;

    /* HH_IGNORE_ERROR[1002] */
    require_once $outfile;
  }

  private function buildGlobalMiddleware(): void {}

  private function buildFactoryContainer(): void {
    $this->interaction->showLine('Scanning project for service factories.');
    $outfile = $this->request->projectRoot().'/build/FactoryContainer.php';
    $dirsToScan = $this->request->projectRoot();
    $filesToScan = Util\listPhpFiles(Vector {$dirsToScan}, null);
    if (\HackPack\HackMini\Container\buildContainer($filesToScan, $outfile)) {
      exit(1);
    }
    ;

    /* HH_IGNORE_ERROR[1002] */
    require_once $outfile;
  }
}
