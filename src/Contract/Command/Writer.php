<?hh // strict

namespace HackPack\HackMini\Contract\Command;

interface Writer
{
    /**
     * Write the string to the stream
     */
    public function write(string $data) : void;
}
