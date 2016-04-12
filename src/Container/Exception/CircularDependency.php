<?hh // strict

namespace HackPack\HackMini\Container\Exception;

class CircularDependency extends \Exception {
  public function __construct(
    private string $name,
    private \ConstSet<string> $namelist,
  ) {
    parent::__construct('Found circular dependency: '.$this->_chain());
  }

  private function _chain(): string {
    return implode(' -> ', $this->namelist).' -> '.$this->name;
  }

  public function chain(): string {
    return $this->_chain();
  }
}
