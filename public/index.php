<?hh

require dirname(__DIR__).'/vendor/autoload.php';
require dirname(__DIR__).'/vendor/hh_autoload.php';

$factory = new FactoryContainer();
$app = $factory->getWebApp();
$app->run();
