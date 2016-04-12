<?hh // strict

namespace HackPack\HackMini\Util;

function recursiveMakeDir(string $path, int $mode = 0755): bool {
  if (is_dir($path)) {
    return true;
  }
  if (is_file($path)) {
    return false;
  }

  $parent = dirname($path);
  if (!recursiveMakeDir($parent)) {
    return false;
  }

  if (is_dir($parent) && is_writable($parent)) {
    mkdir($path, $mode);
    return true;
  }

  return false;
}
