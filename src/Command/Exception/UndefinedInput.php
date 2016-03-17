<?hh // strict

namespace HackPack\HackMini\Command\Exception;

use HackPack\HackMini\Command\Request;

class UndefinedInput extends \Exception
{
    public function __construct(private Request $request, private string $inputName)
    {
        parent::__construct();
    }

    public function request() : Request
    {
        return $this->request;
    }

    public function inputName() : string
    {
        return $this->inputName;
    }
}
