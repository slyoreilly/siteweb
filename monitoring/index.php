<?php
define('ROOT', dirname(__DIR__).DIRECTORY_SEPARATOR.'monitoring'.DIRECTORY_SEPARATOR);
define('APP',ROOT.'app'.DIRECTORY_SEPARATOR);
define('VIEW',ROOT.'app'.DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR);
define('DATA',ROOT.'app'.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR);
define('MODEL',ROOT.'app'.DIRECTORY_SEPARATOR.'model'.DIRECTORY_SEPARATOR);
define('CORE',ROOT.'app'.DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR);
define('CONTROLLER',ROOT.'app'.DIRECTORY_SEPARATOR.'controller'.DIRECTORY_SEPARATOR);
$modules=[ROOT,APP,CORE,CONTROLLER,DATA];
set_include_path(get_include_path().PATH_SEPARATOR.implode(PATH_SEPARATOR,$modules));
spl_autoload_register('spl_autoload',false);
//var_dump($_SERVER['REQUEST_URI']);
//include_once($_SERVER['DOCUMENT_ROOT'].'/monitoring/app/core/Application.php');
//print_r(error_get_last());
include_once(CORE.'Application.php');
include_once(CORE.'Controller.php');
include_once(CORE.'View.php');
include_once(CONTROLLER.'homeController.php');
include_once(CONTROLLER.'alarmController.php');
include_once(CONTROLLER.'appareilController.php');
include_once(DATA.'Database.php');
//include_once(MODEL.'Alarm.php');
//print_r(error_get_last());
new Application;//();
//print_r(error_get_last());
//var_dump(get_include_path());
?>