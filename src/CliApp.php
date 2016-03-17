<?hh // strict

namespace HackPack\HackMini;


class CliApp
{
    public function __construct(
        private Command\Request $request,
        private Command\UserInteraction $interaction,
    )
    {
    }

    public function run() : int
    {
        $router = new Router\Command(
            new \FactoryContainer(),
            $this->commandList(),
            globalCliMiddleware()
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

    private function commandList() : Map<string, Command\Definition>
    {
        $list = commands();
        if($list->isEmpty()) {
            $this->buildCommands();

            // Should never get here
            exit(1);
        }

        return $list;
    }

    private function buildCommands() : void
    {
         // TODO: invoke the commands:build command and try again in a subshell
    }
}
