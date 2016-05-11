<?hh

require_once dirname(__DIR__).'/vendor/hh_autoload.php';

$root = dirname(__DIR__).'/test';
$includes = Set {$root};
$excludes = Set {$root.'/Fixtures', $root.'/Doubles'};
$options = new HackPack\HackUnit\Util\Options($includes, $excludes);

HackPack\HackUnit\HackUnit::run($options);
