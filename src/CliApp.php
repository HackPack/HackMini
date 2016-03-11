<?hh // strict

namespace HackPack\HackMini;


class CliApp
{
    public function __construct(
        private Vector<string> $args,
        private string $rootPath,
    )
    {
        if($args->count() < 1) {
            throw new \UnexpectedValueException('The raw list of arguments from the command line must have at least 1 element.');
        }
    }

    public function run() : int
    {
        $router = new Router\Command(
            new \FactoryContainer(),
            $this->commandList(),
            globalCliMiddleware()
        );
        return $router->dispatch(
            $this->getRequest(),
            Command\UserInteraction::fromEnv(),
        );
    }

    private function getRequest() : Command\Request
    {
        $invocation = array_shift($this->args);

        if($this->args->count() < 1) {
             return new Command\Request('help', $this->args, $this->rootPath);
        }

        $command = array_shift($this->args);

        return new Command\Request($command, $this->args, $this->rootPath);
    }

    private function commandList() : Map<string, Command\Definition>
    {
        $list = commands();
        if($list->isEmpty()) {
            $this->buildCommands();
            exit(1);
        }

        return $list;
    }

    private function buildCommands() : void
    {
         // TODO: invoke the commands:build command and try again in a subshell
    }
}
