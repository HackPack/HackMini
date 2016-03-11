<?hh // strict

class FactoryContainer
{
    public function getDb() : HackPack\HackMini\Sample\PdoDb
    {
        return HackPack\HackMini\Sample\PdoDb::factory($this);
    }

    public function getMockDb() : HackPack\HackMini\Sample\MockDb
    {
        return HackPack\HackMini\Sample\MockDb::factory($this);
    }

    public function getUserStore() : HackPack\HackMini\Sample\UserStore
    {
        return HackPack\HackMini\Sample\UserStore::factory($this);
    }

    public function getAuth() : HackPack\HackMini\Sample\Auth
    {
        return HackPack\HackMini\Sample\Auth::factory($this);
    }

    public function getNewUserPage() : NewUserPage
    {
         return NewUserPage::factory($this);
    }

    public function getUserDetailPage() : UserDetailPage
    {
        return UserDetailPage::factory($this);
    }

    public function getRequireUser() : HackPack\HackMini\Sample\RequireUser
    {
        return HackPack\HackMini\Sample\RequireUser::factory($this);
    }
}
