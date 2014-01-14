<?php

/**
 *包含必需的配置信息
 */
include_once '../sys/config/db-cred.inc.php';

/**
 *为配置文件定义常量
 */

foreach($C as $name =>$val){
	define($name,$val);
}

/**
 *生成一个PDO对象
 */
$dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME;
$dbo = new PDO($dsn,DB_USER,DB_PASS);
//var_dump($dbo);
/**
 *定义自动载入的_autoload函数
 */
function __autoload($class){
	$filename = "../sys/class/class.".$class.".inc.php";
	if(file_exists($filename)){
			include_once $filename;
			
	}
}


?>
