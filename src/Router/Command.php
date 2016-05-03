<?hh // strict

namespace HackPack\HackMini\Router;

use HackPack\HackMini\Command\Request;
use HackPack\HackMini\Command\Handler;
use HackPack\HackMini\Command\UserInteraction;
use HackPack\HackMini\Command\ArgumentDefinition;
use HackPack\HackMini\Command\OptionDefinition;
use HackPack\HackMini\Command\Definition;
use HackPack\HackMini\Contract\Middleware;

type RequestBuilder = (function(Vector<string>): Request);

class Command {
  private Vector<Middleware<Request, UserInteraction, int>> $stack;
  public function __construct(
    private \FactoryContainer $c,
    private \ConstMap<string, Definition> $commandList,
    \ConstVector<Middleware<Request, UserInteraction, int>> $globalMiddleware,
  ) {
    $this->stack = $globalMiddleware->toVector();
  }

  public function dispatch(Request $req, UserInteraction $interaction): int {
    $commandDefinition = $this->commandList->get($req->name());

    if ($commandDefinition === null) {
      $interaction->showLine('Unable to find command "'.$req->name().'"');
      $interaction->showList($this->commandList->keys());
      return 1;
    }

    $this->stack
      ->addAll($commandDefinition['middleware']->map($m ==> $m($this->c)));

    $req =
      $req->withArguments($commandDefinition['arguments'])
        ->withOptions($commandDefinition['options']);

    return $this->runStack($commandDefinition['handler'], $req, $interaction);
  }

  private function runStack(
    Handler $handler,
    Request $req,
    UserInteraction $interaction,
  ): int {
    if ($this->stack->isEmpty()) {
      return $handler($this->c, $req, $interaction);
    }

    $current = array_shift($this->stack);

    $next = ($req, $interaction) ==> {
      return $this->runStack($handler, $req, $interaction);
    };

    return $current->handle($req, $interaction, $next);
  }
}
