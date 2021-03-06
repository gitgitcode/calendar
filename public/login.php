<?php
	/**
	* 包含必须的（初始化）文件
	*/
	include_once '../sys/core/init.inc.php';

	/**
	* 输出页头
	*/
	$page_title = 'Please Log In';
	$css_files = array('style.css','admin.css');
	include_once 'assets/common/header.inc.php';

?>
<div id="content">
<form action="assets/inc/process.inc.php" method="post">
		<fieldset>
			<legend>
				Please Log In
			</legend>
			<label for='uname'>
			Username					
			</label>
			<input type="text" name="uname" id="uname" value="" />
			<label for="pword">
				Password
			</label>
			<input type="password" name="pword" value="" />
			<input type="hidden" name="token" value="<?php echo $_SESSION['token'];?>" />
			<input type="hidden" name="action" value="user_login" />
			<input type="submit" name="login_submit" value="Log In" />
			or <a href="./">cancel</a>
		</fieldset>
</form>

</div><!-- end #content -->


<?php 
/**
*输出页头
*/
include_once 'assets/common/footer.inc.php';
?>