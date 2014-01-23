<?php
/**
 *确保传入活动ID
 */
if(isset($_POST['event_id']))
{
	/**
	 *从url参数中得到活动ID
	 */
	$id = (int)$_POST['event_id'];

}else{
	/**
	 *如果未得到活动活动ID，重定向到主页
	 */
	header("Location: ./");
	exit;
}

/**
 *包含初始化文件
 */
include_once '../sys/core/init.inc.php';

/**
 * 载入日历
 */

$cal = new Calendar($dbo);
$markup = $cal->confirmDelete($id);

/**
 *输出页头
 */
$page_title = "View Event";
$css_files = array('style.css','admin.css');
include_once 'assets/common/header.inc.php';


?>

<div id="content">
<?php echo $markup; ?>
</div><!--end #content-->

<?php
/**
 *输出页尾
 */
include_once 'assets/common/footer.inc.php';
?>
