<?php 
//class.admin.inc.php
/**
*管理（添加，修改等）行为
* PHP version 5
*
* @author chao
* @copyright 2014 
* @license mit-license
*/

class Admin ectends DB_Connet{

	/**
	* 确定用于散列密码中的盐长度
	* @var int 用于密码盐的字符串的长度
	*/
	private $_saltLength = 7;

	/**
	* 保存或生成一个DB对象，设定盐的长度
	* @param object $db 数据库对象
	* @param int $saltLength 密码盐值得长度
	*/
	public function __construct($db=NULL,$saltLength=NULL)
	{
		parent:: __construct($db);
		/**
		* 若传入一个整数，则用它来设定saltLength的值
		*/
		if(is_int($saltLength)){
			$this->_saltLength = $saltLength;
		}


	}
		}

}

?>
