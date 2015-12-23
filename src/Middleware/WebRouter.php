<?hh // strict

namespace HackPack\HackMini\Middleware;

use HackPack\HackMini\HttpVerb;
use HackPack\HackMini\FactoryContainer;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class WebRouter implements Handler
{
    private static string $delimiter = '~';

    public function __construct(
        private Map<HttpVerb,Map<string,(function(Request,Response,FactoryContainer):Response)>> $routes,
        private FactoryContainer $container,
        private (function(Request,Response,FactoryContainer):Response) $notFound,
    )
    {
    }

    public function handle(Request $req, Response $rsp, (function(Request,Response):Response) $next): Response
    {
        $path = $req->getUri()->getPath();
        foreach($this->extractHandlers($req) as $pattern => $handler) {
            // Ensure the delimiting character is escaped
            $matches = [];
            $matchFound = preg_match($this->patternToRegex($pattern), $path, $matches);
            /* HH_FIXME[4118] regex pattern is set by user of framework */
            if($matchFound === false) {
                throw new \InvalidArgumentException('Route pattern ' . $pattern . ' is not a valid regular expression.');
            }
            if($matchFound === 1) {
                return $handler($req->withAttribute('matches', new Vector($matches)), $rsp, $this->container);
            }
        }
        $nf = $this->notFound;
        return $nf($req, $rsp, $this->container);
    }

    private function extractHandlers(Request $req) : Map<string,(function(Request,Response,FactoryContainer):Response)>
    {
        $verb = HttpVerb::coerce(strtoupper($req->getMethod()));
        $verb = $verb === null ? HttpVerb::Any : $verb;
        return $this->routes->containsKey($verb) ?
            $this->routes->at($verb) :
            Map{};
    }

    private function patternToRegex(string $pattern) : string
    {
        // Ensure there is a single ^ at the start and $ at the end
        if($pattern[0] !== '^') {
            $pattern = '^' . $pattern;
        }
        if($pattern[strlen($pattern) - 1] === '$' && $pattern[strlen($pattern) - 2] !== '\\') {
            $pattern = $pattern . '$';
        }

        $pattern = str_replace('~', '\~', $pattern);
        return "~{$pattern}~";
    }
}
