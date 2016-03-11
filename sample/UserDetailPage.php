<?hh // strict

use HackPack\HackMini\Sample\User;

final class UserDetailPage
{
    <<Provides('UserDetailPage')>>
    public static function factory(FactoryContainer $c) : this
    {
        return new static();
    }

    public function publicProfile(User $newUser) : string
    {
        return (string)
            <html>
                <head/>
                <body>
                    <pre>{var_export($newUser)}</pre>
                </body>
            </html>;
    }

    public function privateProfile(User $me) : string
    {
         return $this->publicProfile($me);
    }

    public function missing() : string
    {
        return (string)
            <html>
                <head/>
                <body>
                    <p>Could not find the selected user</p>
                </body>
            </html>;
    }
}
