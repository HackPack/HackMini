<?hh // strict

namespace HackPack\HackMini\Sample;

use FactoryContainer;

type User = shape(
    'id' => int,
    'name' => string,
    'title' => ?string,
);

final class UserStore
{
    <<Provides('UserStore')>>
    public static function factory(FactoryContainer $c) : this
    {
        return new static($c->getDb());
    }

    <<Provides('MockUserStore')>>
    public static function mocked(FactoryContainer $c) : this
    {
        return new static($c->getMockDb());
    }

    public function __construct(private Db $db) { }

    public function fromId(int $id) : ?User
    {
        // Use $this->db to get the user from the id
        return null;
    }

    public function fromAuthToken(string $token) : ?User
    {
        // Use $this->db to get the user from the auth token
        return null;
    }

    public function create(string $name, ?string $title = null) : User
    {
        // Use $this->db to create the user
        return shape(
            'id' => 0,
            'name' => $name,
            'title' => $title,
        );
    }
}
