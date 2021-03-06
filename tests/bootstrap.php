<?php
// IF NOT RUNNING PHPUNIT, EXIT DIRECTLY!!
if (! defined('PHPUNIT_RUNNING')) { exit(); }

defined('DS') || define('DS', DIRECTORY_SEPARATOR);

// Require composer autoload!
require_once dirname(__DIR__) .DS .'vendor' .DS .'autoload.php';

/** Define the absolute paths for configured directories (changed for tests) */
define('BASEPATH', realpath(dirname(__DIR__)).DS);
// The Application paths.
define('WEBPATH', BASEPATH.'public'.DS);
define('APPPATH', BASEPATH.'app'.DS);
define('SYSPATH', BASEPATH.'system'.DS);


use Doctrine\Common\Annotations\AnnotationRegistry;
use Nova\Modules\Manager as Modules;
use Nova\Events\Manager as Events;
use Nova\Net\Router;
use Nova\Config;


/**
 * CUSTOMIZED CONFIG BOOTSTRAP
 */
$configDir = BASEPATH .'app' .DS .'Config' .DS;

require_once $configDir.'constants.example.php';

require_once SYSPATH. 'Config' .DS .'functions.php';

require_once $configDir.'config.example.php';


/**
 * Our test db config, replace it here.:
 */
Config::set('database', array(
    'default' => array(
        'engine' => 'mysql',
        'driver' => 'pdo_mysql',
        'config' => array(
            'host'        => 'localhost',
            'port'        => 3306,     // Not required, default is 3306
            'dbname'      => 'testdb1',
            'user'        => 'root',
            'password'    => '',
            'charset'     => 'utf8',   // Not required, default and recommended is utf8.
            'return_type' => 'object'  // Not required, default is 'array'.
        )
    ),
    'sqlite' => array(
        'engine' => 'sqlite',
        'driver'  => 'pdo_sqlite',
        'config' => array(
            'path'        => BASEPATH .'storage'.DS.'persistent'.DS.'test.sqlite',
            'return_type' => 'object' // Not required, default is 'array'.
        )
    )
));

/**
 * Set timezone.
 */
date_default_timezone_set(Config::get('timezone'));

/**
 * Start sessions.
 */
//Session::init(); // No session in tests!

/**
 * Get the Router instance.
 * */
$router = Router::getInstance();

/**
 * load routes
 * */
// require_once $configDir.'routes.php'; // Default routes, we will only change the routes later in tests!

/**
 * bootstrap the active modules (and their associated routes)
 * */
Modules::bootstrap();

/**
 * initialize the Events
 * */
Events::initialize();

// Let the errors be reported so we could see them in our consoles!
error_reporting(E_ALL);
set_exception_handler(null);
set_error_handler(null);

/** Register Annotation Namespace */
$annotationPath = SYSPATH . "ORM" . DS . "Annotation" . DS;
AnnotationRegistry::registerFile($annotationPath . 'Table.php');
AnnotationRegistry::registerFile($annotationPath . 'Column.php');
AnnotationRegistry::registerFile($annotationPath . 'Relation.php');