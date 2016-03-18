<?hh // strict

namespace HackPack\HackMini\Contract\Command;

interface Reader
{
    /**
     * Read maximum $bytes bytes of data from the stream
     *
     * If fewer bytes are available, return the empty string.
     */
    public function read(int $bytes) : string;

    /**
     * Continue to read bytes from the stream until a newline is detected.
     *
     * Do not include the newline character in the result.
     */
    public function readline() : string;
}
