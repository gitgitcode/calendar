<?php
/**
 *确保传入活动ID
 */
if(isset($_GET['event_id']))
{
	/**
	 *确保传入的ID是整数
	 */
	$id = preg_replace('/[^0-9]/','',$_GET['event_id']);

	/**
	 *如果传入的ID不是整数，重定向到主页面
	 */
	if(empty($id))
	{
		header("Location:./");
		exit;
	}
}else{
/**
 *没有传入活动ID，重定向到主页面
 */
	header("Loaction:./");
	exit;
}

include_once '../sys/core/init.inc.php';
/**
 * 输出页头
 */
$page_title = "View Event";
$css_files = array("style.css");
include_once 'assets/common/header.inc.php';
$cal = new Calendar($dbo);
?>
<div id="content">
<?php echo $cal->displayEvent($id);?>
<a href="./">&laquo;BACK to the calendar</a>
</div><!--end #content-->
<?php
include_once 'assets/common/footer.inc.php';
?>
