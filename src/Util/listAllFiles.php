<?hh // strict
namespace HackPack\HackMini\Util;

require_once __DIR__ . '/every.php';

use \SplFileInfo;

<<__Memoize>>
function listPhpFiles(
    ?\ConstVector<string> $include,
    ?\ConstVector<string> $exclude,
    bool $skipHiddenFiles = true,
    ?\ConstSet<string> $allowedExtensions = null,
) : Vector<SplFileInfo>
{
    if($allowedExtensions === null) {
        $allowedExtensions = Set{'php', 'hh'};
    }
    if($include === null || $include->isEmpty()) {
        $include = Vector{getcwd()};
    }

    if($exclude === null) {
        $exclude = Vector{};
    }

    $include = $include->map($p ==> new SplFileInfo($p));
    $exclude = $exclude->map($p ==> new SplFileInfo($p));

    $pathIsAllowed = (SplFileInfo $path) ==> {

        if($skipHiddenFiles && substr($path->getFilename(), 0, 1) === '.') {
            return false;
        }

        if($path->isFile() && !$allowedExtensions->contains($path->getExtension())) {
             return false;
        }

        return every(
            $exclude,
            $excludeInfo ==> $excludeInfo->getRealPath() !== $path->getRealPath()
        );
    };

    $files = Vector{};
    foreach($include as $base) {
        if($base->isFile() && $base->isReadable()) {
            $files->add($base);
            continue;
        }

        if(!$base->isDir() || !$base->isReadable()) {
            continue;
        }

        $rdi = new \RecursiveDirectoryIterator(
            $base->getRealPath(),
            \FilesystemIterator::SKIP_DOTS |
            \FilesystemIterator::NEW_CURRENT_AND_KEY,
        );
        /* HH_FIXME[4105] Need definition of RecursiveCallbackFilterIterator in builtin hhi */
        $rfi = new \RecursiveCallbackFilterIterator($rdi, $pathIsAllowed);
        $rii = new \RecursiveIteratorIterator($rfi);
        foreach($rii as $file) {
            $files->add($file);
        }
    }

    return $files;
}
