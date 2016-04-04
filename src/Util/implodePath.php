<?hh // strict

namespace HackPack\HackMini\Util;

function implodePath(\ConstVector<string> $parts): string {
  if ($parts->isEmpty()) {
    return '';
  }

  $absolute = substr($parts->at(0), 0, 1) === DIRECTORY_SEPARATOR;
  $result = implode(
    DIRECTORY_SEPARATOR,
    $parts->map($p ==> trim($p, DIRECTORY_SEPARATOR)),
  );

  return $absolute ? DIRECTORY_SEPARATOR.$result : $result;
}
