<?hh // strict

final class NewUserPage
{
    <<Provides('NewUserPage')>>
    public static function factory(FactoryContainer $c) : this
    {
        return new static();
    }

    public function render(HackPack\HackMini\Sample\User $user) : string
    {
        return '';
    }
}
