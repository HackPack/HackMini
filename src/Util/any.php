<?hh // strict

namespace HackPack\HackMini\Util;

function any<T>(Traversable<T> $list, (function(T): bool) $fn): bool {
  foreach ($list as $v) {
    if ($fn) {
      return true;
    }
  }
  return false;
}
