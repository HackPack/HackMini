<?hh // strict

namespace HackPack\HackMini;

class FactoryContainer
{
    private Vector<string> $currentlyBuilding = Vector{};

    private function build<Tobj>(string $name, (function(FactoryContainer):Tobj) $factory): Tobj
    {
        if($this->currentlyBuilding->linearSearch($name) > -1) {
            throw new Exception\CircularDependency($name, $this->currentlyBuilding);
        }
        $this->currentlyBuilding->add($name);
        $obj = $factory($this);
        if($name !== $this->currentlyBuilding->pop()) {
            throw new \RuntimeException();
        }
        return $obj;
    }
}
