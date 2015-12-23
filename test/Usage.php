<?hh // decl

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

function bootstrap(): void
{
    // Create core classes
    $manager =
    $router = new MiddlewareRouter();
    $container = new Container();

    // Configure classes
    $manager->add($router);
    $manager->addFunc(($req, $rsp, $next) ==> {
        // Simple handler as closure
        return $next($req, $rsp);
    });

    $manager->handle(Request::fromWeb(), Response::forWeb());
}

