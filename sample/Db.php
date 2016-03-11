<?hh // strict

namespace HackPack\HackMini\Sample;

use FactoryContainer;

interface Db
{

}

final class PdoDb implements Db
{
    <<Provides('Db')>>
    public static function factory(FactoryContainer $c) : this
    {
        return new static();
    }
}

final class MockDb implements Db
{
    <<Provides('MockDb')>>
    public static function factory(FactoryContainer $c) : this
    {
        return new static();
    }
}
