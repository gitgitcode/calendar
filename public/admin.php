<?php
/**
 *包含初始化文件
 */
include_once '../sys/core/init.inc.php';

/**
 *输出页头
 */
$page_title = "Add/Edit Event";
$css_files = array('style.css');
include_once 'assets/common/header.inc.php';

/**
 *载入日历
 */

$cal=new Calendar($dbo);

?>
<div id="content">
<?php echo $cal->displayForm();?>
</div><!--end #cont-->
<?php
/**
 *输出页尾
 */
include_once 'assets/common/footer.inc.php';
?>
