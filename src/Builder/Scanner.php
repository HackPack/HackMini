<?hh // strict

namespace HackPack\HackMini\Builder;

use FredEmmott\DefinitionFinder\BaseParser;
use FredEmmott\DefinitionFinder\FileParser;
use FredEmmott\DefinitionFinder\TreeParser;
use SplFileInfo;

class Scanner
{
    private Vector<SplFileInfo> $basePaths;
    private FactoryContainer $factoryContainer;
    private Autoloader $autoloader;

    public function __construct(Vector<string> $basePaths)
    {
        $this->factoryContainer = new FactoryContainer();
        $this->autoloader = new Autoloader();
        $this->basePaths = $basePaths->map($p ==> new SplFileInfo($p));
    }

    public function scan() : this
    {
        foreach($this->basePaths as $finfo) {
            if($finfo->isFile() && $finfo->isReadable()) {
                $this->extractFromParser(FileParser::FromFile($finfo->getFilename()));
            }
            if($finfo->isDir() && $finfo->isReadable()) {
                $this->extractFromParser(TreeParser::FromPath($finfo->getFilename()));
            }
        }
        return $this;
    }

    private function extractFromParser(BaseParser $parser) : void
    {
        $this->factoryContainer->addClasses($parser->getClasses());
        $this->factoryContainer->addFunctions($parser->getFunctions());
    }
}
