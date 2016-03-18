<?hh // strict

namespace HackPack\HackMini\Command;

use HackPack\HackMini\Contract\Command\Reader;
use HackPack\HackMini\Contract\Command\Writer;

class StdIO implements Reader, Writer
{
    private resource $in;
    private resource $out;

    public function __construct()
    {
        $this->in = fopen('php://stdin', 'r');
        $this->out = fopen('php://stdout', 'w');
    }

    public function read(int $bytes) : string
    {
        $result = fread($this->in, $bytes);
        if(is_string($result)) {
            return $result;
        }
        return '';
    }

    public function readline() : string
    {
        return trim(fgets($this->in), PHP_EOL);
    }

    public function write(string $data) : void
    {
        fwrite($this->out, $data, strlen($data));
    }

    public function __destruct() : void
    {
        if(is_resource($this->in)) {
            fclose($this->in);
        }
        if(is_resource($this->out)) {
            fclose($this->out);
        }
    }

}
