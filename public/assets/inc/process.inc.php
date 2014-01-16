<?php
/**
 *启动session
 */
session_start();

/**
 *包含初始化文件
 */
//include_once '../../../sys/core/init.inc.php';
include_once '../../../sys/config/db-cred.inc.php';

/**
 *为配置信息定义常量
 */

foreach($C as $name=>$val)
{
	define($name,$val);
}


$dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME;

$dbo = new PDO($dsn,DB_USER,DB_PASS);

//var_dump($C);
/**
 *以表单action为键生成一个关联数组(查找表)
 */
$actions = array(
		'event_edit'=>array(
			'object'=>'Calendar',
			'method'=>'processForm',
			'header'=>'Location: ../../'
		)
);

/**
 *保证session 中的防跨站标记与提交过来的标记一致及请求action合法(在关联数组中)
 */

//var_dump($_POST);exit();
if($_POST['token']==$_SESSION['token'] && isset($actions[$_POST['action']]))
{
		$use_array = $actions[$_POST['action']];
		$obj = new $use_array['object']($dbo);
		if(True === $msg=$obj->$use_array['method']())
		{
				//var_dump($msg);
			//	echo 111;
			header($use_array['header']);
			exit;
		}else{
			//	echo 999;
			//如果出错，输出错误信息并退出程序
			die($msg);
		}
}else{
	//	echo 888;
//如果token/action非法，重定向到主页
header("Location: ../../");
exit;

}

function __autoload($class_name)
{
	$filename = '../../../sys/class/class.'.strtolower($class_name).'.inc.php';
	if(file_exists($filename))
	{
		include_once $filename;
	}
}
?>
