<?php
/**
 *包含初始化文件
 */
include_once '../sys/core/init.inc.php';

/**
 *载入1月份日历
 */

$cal = new Calendar($dbo,"2014-01-01 00:00:00");

/**
 * 初始化标题和样式文件
 */
$page_title = "Events Calendar";
$css_files = array('style.css','admin.css');

/**
 *包含页头
 */
include_once 'assets/common/header.inc.php';

/**
 *测试
 */
//$info=$cal->buildCalendar();
//var_dump($info);
//echo '<hr>';
//if(is_object($cal)){
//		echo "<pre>".var_dump($cal)."<pre/>";
///}
?>
<div id="content">
<?php
/**
 *生成并显示日历html
 */
echo $cal->buildCalendar();
var_dump($cal->test());
?>
</div><!--end #content-->
<?php
/**
 *包含页尾
 */
include_once 'assets/common/footer.inc.php';
?>
