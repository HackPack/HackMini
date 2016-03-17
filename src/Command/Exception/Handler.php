<?hh // strict

namespace HackPack\HackMini\Command\Exception;

use HackPack\HackMini\Command\UserInteraction;

class Handler
{
    public function __construct(
        private UserInteraction $interaction
    ) { }

    public function handle(\Exception $e) : int
    {
        return 1;
    }
}