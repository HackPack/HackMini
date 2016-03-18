<?hh // strict

namespace HackPack\HackMini;


class CliApp
{
    public function __construct(
        private Command\Request $request,
        private Command\UserInteraction $interaction,
    )
    {
        if(!function_exists('commands')) {
            $this->buildCommands();
        }

        if(!function_exists('globalCliMiddleware')) {
            $this->buildGlobalMiddleware();
        }

        if(!class_exists('FactoryContainer')) {
            $this->buildFactoryContainer();
        }
    }

    public function run() : int
    {
        $router = new Router\Command(
            new \FactoryContainer(),
            \commands(),
            \globalCliMiddleware()
        );

        try {
            return $router->dispatch(
                $this->request,
                $this->interaction,
            );
        } catch (\Exception $e) {
            $handler = new Command\Exception\Handler($this->interaction);
            return $handler->handle($e);
        }
    }

    private function buildCommands() : void
    {
         $this->interaction->showLine('Scanning project for commands.');
         $outfile = $this->request->projectRoot() . '/commands.php';
         $dirsToScan = $this->request->projectRoot();
         $filesToScan = Util\listPhpFiles(Vector{$dirsToScan}, null);
         if (Command\buildCommands($filesToScan,$outfile)) {
              exit(1);
         };
    }

    private function buildGlobalMiddleware() : void
    {
    }

    private function buildFactoryContainer() : void
    {
    }
}
