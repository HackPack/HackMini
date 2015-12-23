<?hh // strict

namespace HackPack\HackMini\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

enum HandlerType : string
{
    Obj = 'Object';
    Fun = 'Function';
}

type FunctionHandler = (function(Request, Response, (function(Request, Response):Response)): Response);

newtype Handlerish = shape(
    'type' => HandlerType,
    'fun' => ?FunctionHandler,
    'obj' => ?Handler,
);

class Manager
{
    private Vector<Handlerish> $handlers = Vector{};
    private Vector<Handlerish> $workingHandlers = Vector{};

    public function __construct(Traversable<Handler> $handlers = [])
    {
        foreach($handlers as $handler) {
            $this->add($handler);
        }
    }

    public function add(Handler $h) : this
    {
        $this->handlers->add(shape(
            'type' => HandlerType::Obj,
            'obj' => $h,
        ));
        return $this;
    }

    public function addFunc(FunctionHandler $h) : this
    {
        $this->handlers->add(shape(
            'type' => HandlerType::Fun,
            'fun' => $h,
        ));
        return $this;
    }

    public function handle(Request $req, Response $rsp) : Response
    {
        if($this->handlers->isEmpty()) {
            return $rsp;
        }
        $this->workingHandlers = $this->handlers->toVector();
        return $this->handleRecursive($req, $rsp);
    }

    private function handleRecursive(Request $req, Response $rsp): Response
    {
        if($this->workingHandlers->isEmpty()) {
            return $rsp;
        }
        $h = $this->workingHandlers->pop();
        switch($h['type']) {
        case HandlerType::Fun:
            $handler = $h['fun'];
            if($handler === null) {
                 throw new \RuntimeException('Function middleware handler missing.');
            }
            break;

        case HandlerType::Obj:
            $handler = $h['obj'];
            if($handler === null) {
                 throw new \RuntimeException('Object middleware handler missing.');
            }
            $handler = inst_meth($handler, 'handle');
            break;
        }

        return $handler($req, $rsp, ($newReq, $newRsp) ==> $this->handleRecursive($newReq, $newRsp));
    }
}
