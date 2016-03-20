<?hh // strict

namespace HackPack\HackMini\Sample;

use FactoryContainer;
use HackPack\HackMini\Message\Request;

final class Auth
{
    const string TOKEN_NAME = 'my-auth-header';

    private ?User $me = null;

    <<Provides('auth')>>
    public static function factory(FactoryContainer $c) : this
    {
        return new static($c->getUserStore());
    }

    public function __construct(
        private UserStore $userStore,
    ) { }

    public function extractUserFromRequest(Request $req) : ?User
    {
        $token = $req->getHeaderLine(self::TOKEN_NAME);
        return $this->userStore->fromAuthToken($token);
    }

    public function me() : ?User
    {
        return $this->me;
    }

    public function meOrThrow() : User
    {
        $me = $this->me;
        if($me === null) {
            throw new \Exception('Must log in');
        }
        return $me;
    }
}
