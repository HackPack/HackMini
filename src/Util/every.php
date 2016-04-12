<?hh // strict

namespace HackPack\HackMini\Util;

function every<T>(Traversable<T> $list, (function(T): bool) $fn): bool {
  foreach ($list as $v) {
    if (!$fn($v)) {
      return false;
    }
  }
  return true;
}
