<?php
/**
 *创建维护活动日程表
 * PHP 5.5
 * @author char
 * @copyright 2014
 * @license
 */
class Calendar extends DB_Connect{
	
	/**
	 * 日历根据此日期构建
	 * 格式为YYY-mm-dd HH：MM：SS
	 * @var string 日历显示日期
	 */
	private $_useDate;

	/**
	 *日历显示月份
	 *@var int 月份
	 */

	private $_m;
	/**
	 *当前显示月份是那一年
	 *@var int 当前年
	 */
	private $_y;
	/**
	 *这个月有多少天
	 *@var int 这个月天数
	 */

	/**
	 *这个月其实周的索引
	 *@var int 这个月从周几开始
	 */

	private $_startDay;
	//方法和属性定义从此处开始
	//
	function __construct($dbo=NUll,$useDate=NUll){
			/**
			 *调用父类构造函数
			 *检查数据库对象
			 */
			parent::__construct($dbo);

			/**
			 *收集并储存该月有关的数据
			 */

			if(isset($useDate)){
			$this->_useDate = $useDate;	
			}else{
			$this->_useDate = date('Y-m-d H:i:s');	
			}
			/**
			 *把日期转换为时间戳，确定日历要显示的年和月
			 */
			$ts = strtotime($this->_useDate);
			$this->_m = date('m',$ts);
			$this->_y = date('Y',$ts);

			/**
			 * 确定这个月有多少天
			 */
			$this->_daysInMonth = cal_days_in_month(
					CAL_GREGORIAN,
					$this->_m,
					$this->_y);
			/**
			 *确定这个月从周几开始
			 */

			$ts = mktime(0,0,0,$this->_m,1,$this->_y);
			$this->_startDay = date('w',$ts);
	}

	/**
	 * 将活动信息载入一个数组
	 * @param int $id 用来过滤结果可选的活动
	 * @return array 来自数据库的活动信息数组
	 */

	private function _loadEventData($id=null){

			$sql="SELECT 
					`event_id`,`event_title`,`event_desc`,
					`event_start`,`event_end`
					FROM `events`";
			/**
			 *如果提供了活动id 则添加where子句，只返回该活动
			 */

			if(!empty($id)){
					$sql.="WHERE `event_id`= :id LIMIT 1";
			}else{
			/**
			 *否则在载入该月所有活动
			 */
				/**
				 *找出这个月第一天和最后一天
				 */

				$start_ts = mktime(0,0,0,$this->_m,1,$this->_y);
				$end_ts = mktime(0,0,0,$this->_m+1,0,$this->_y);
				$start_date = date('Y-m-d H:i:s',$start_ts);
				$end_date = date('Y-m-d H:i:s',$end_ts);
				/**
				 *找出当前月份的活动
				 */
				$sql.= "WHERE `event_start` BETWEEN '$start_date' AND '$end_date' ORDER BY `event_start`";
//echo $sql;
			}
			try{ 
				//	echo $sql;
					$stm = $this->db->prepare($sql);
			/**
			 *如果ID有效则绑定此参数
			 */
					if(!empty($id)){
					$stm ->bindParam(":id",$id,PDO::PARAM_INT);
						
					}
					$stm ->execute();
					$results = $stm->fetchAll(PDO::FETCH_ASSOC);
					$stm->closeCursor();
					 //var_dump($results);
					return $results;
			}	
			catch (Exception $e){
				die($e->getMessage());	
			}
	}

	/**
	 *载入该月全部活动信息到一个数组
	 *@return array 活动信息
	 */	
	private function _createEventObj(){
	/**
	 *载入活动数组
	 */	
	$arr = $this->_loadEventData();
	/**
	 *按照活动发生在该月第几天将活动数据重新组织到一个新数组中
	 */
	$evnents = array();
		foreach($arr as $event){
			$day = date('j',strtotime($event['event_start']));
			try{
				$events[$day][]=new Event($event);//继承class.event.inc.php

			}
			catch (Exception $e){
			die($e->getMessage());
			}
		}

	//var_dump($events);

	return $events;
	}
	

/**
 *生成用于显示日历和活动的html标记 使用
 *储存在类中的属性，载入给定的月份的活动数据，
 *生成并返回完成的日历html标记
 *@return string 日历html标记
 */	

	public function buildCalendar(){
		/**
		 *确定日历显示月份并创建一个用于标识日历每列星期几的缩写组
		 */	

		$cal_month = date('F Y',strtotime($this->_useDate));
		$weekdays = array('Sun','Mon','Tue','Wed','Thu','Fri','Sat');

		/**
		 *给日历标记添加一个标题
		 */
		$html = "\n\t<h2>$cal_month</h2>";
		for($d=0,$labels=null;$d<7;++$d){
			$labels .= "\n\t\t<li>".$weekdays[$d]."</li>";	
		}
		$html .="\n\t<ul class=\"weekdays\">".$labels."\n\t</ul>";
		
		//-------------
		/**
		 *载入活动数据
		 */
		$events = $this->_createEventObj();

		//----------
		/**
		 *生成日历html标记
		 */	
		$html .="\n\t<ul>";//开始一个新的<ul>;
		for($i=1,$c=1,$t=date('j'),$m=date('m'),$y=date('Y');
		$c<=$this->_daysInMonth;++$i){
		
				//echo $i;
		//--3-5--
		/**
		 *为起始日之前的几天添加class fill
		 */	
			$class = $i<= $this->_startDay?"fill":null;
			/**
			 *如果当前处理日期是今天，则为它添加class today
			 */
			/*echo $t.'<br/>';
			echo '<hr/>';
			echo $c."--<br/>";*/
			if($c==$t && $m==$this->_m && $y==$this->_y){
				$class ="today";
			}
			/**
			 *生成列表<li>的开始和结束标记
			 */
			$ls = sprintf("\n\t\t<li class=\"%s\">",$class);
			$le = "\n\t\t</li>";
			
			//---6-10---
			/**
			 *添加日历盒的主体，内容是该月的每一天
			 */
			$event_info =null;
			if($this->_startDay < $i && $this->_daysInMonth >=$c){
					//-----------
					if(isset($events[$c])){
						foreach($events[$c] as $event){
							$link = '<a href="view.php?event_id='.$event->id.'">'.$event->title.'</a>';
							$event_info .="\n\t\t\t$link";
						}
					}
					//------------
					//	echo $c.'<br/>';
				
					$date = sprintf("\n\t\t\t<strong>%02d</strong>",$c++);				 
				
				//	var_dump($date);
				//	echo $c.'<br/>';

			}else{
				$date="&nbsp;";
			}
			/**
			 *如果赶上周六，就其新的一行
			 */
			$wrap = $i!=0 && $i%7==0 ?"\n\t</ul>\n\t<ul>":null;
			/**
			 *将上面的组装成一个
			 */
			$html.= $ls.$date.$event_info.$le.$wrap;
			 //echo $c.'<br/>';
			}

		/**
		 *为最后一周的几天添加填充项
		 */
		while($i%7!=1){
		$html.="\n\n\t<li class=\"fill\">&nbsp;</li>";
		++$i;	
		}
		/**
		 *关闭最后一个<ul>标签
		 */	
		$html.="\n\t</ul>\n\n";	

		//p148 
		//
		/**
		 *若用户已登录，显示管理选项
		 */
		$admin = $this->_adminGeneralOptions();

		/**
		 *返回用于输出的html标记
		 */
		return $html.$admin;
	}	
	
	/**
	 *p 131 生成标记
	 * 得到活动信息HTML
	 * @param int $id活动ID
	 * @return string 用于显示活动信息的基本HTML标记
	 *
	 */
	public function displayEvent($id)
	{
		/**
		 *确保传入有效ID
		 */
		if(empty($id)){return NULL;}
		/**
		 *确保ID是整数
		 */
		$id = preg_replace('/[^0-9]/','',$id);
		/**
		 *从数据库中载入活动数据
		 */
		$event =$this->_loadEventById($id);

		/**
		 *为$date,$start,$end 变量生成相应的字符串
		 */
		$ts = strtotime($event->start);
		$date = date('F d,Y',$ts);
		$start = date('g:ia',$ts);
		$end = date('g:ia',strtotime($event->end));

		//p153
		//
		/**
		 *若用户已登录，再入管理选项
		 *
		 */
		$admin = $this->_adminEntryOptions($id);

		/**
		 *生成并返回HTML标记
		 */
		return "<h2>$event->title</h2>".
				"\n\t<p class=\"dates\">$date,$start&mdash;$end</p>".
				"\n\t<p>$event->description</p>$admin";
	}


	/**
	 * 功能-检查传入id是否为整，
	 * 声明一个空变量保存活动的描述信息
	 * 传入数据有效则载入活动数据，
	 * 若找到活动数据将他赋值给前面的声明变量
	 * 输出表单标记
	 * p136
	 *生成一个修改或创建活动的表单
	 */
	public function displayForm()
	{
		/**
		 *检查是否传入活动ID
		 */
		if(isset($_POST['event_id']))
		{
			$id = (int)$_POST['event_id'];//强制类型转换确保数据安全
		}else{
			$id = NULL;
		}
				//$id=1; //for test
		/**
		 *标题/提交按钮文本
		 */
		$submit = "Create a New Event";
		/**
		 *若传入活动ID则载入相应的数据
		 */
		if(!empty($id))
		{
			$event = $this->_loadEventById($id);
			/**
			 *若未找到响应的活动返回null
			 */
			if(!is_object($event)){return NULL;}

			$submit = "Edit This Event";
		
		/**
		 *生成标记
		 */
		return <<<FORM_MARKUP
		<form action="assets/inc/process.inc.php" method="POST">
			<fieldset>
				<legend>{$submit}</legend>
				<label for="event_title">Event Title</lable>
				<input type="text" name="event_title" 
						id="event_title" value="$event->title"/>
				<label for="event_start">Start Time</lable>
				<input type="text" name="event_start"
						id="evetn_start" value="$event->start"/>
				<label for="event_end">End Time</lable>
				<input type="text" name="event_end"
						id="event_end" value="$event->end"/>
				<label for="event_description">Event Description</lable>
				<textarea name="event_description">$event->description</textarea>
				<input type="hidden" name="event_id" value="$event->id" />
				<input type="hidden" name="token" value="$_SESSION[token]"/>				<input type="hidden" name="action" value="event_edit" />
				<input type="submit" name="event_submit" value="$submit" />
			or <a href="./">cancel</a>
			</fieldset>
		</from>
FORM_MARKUP;
		}else{
		
		return <<<FORM_MARKUP
		<form action="assets/inc/process.inc.php" method="POST">
			<fieldset>
				<legend>{$submit}</legend>
				<label for="event_title">Event Title</lable>
				<input type="text" name="event_title" 
						id="event_title" value=""/>
				<label for="event_start">Start Time</lable>
				<input type="text" name="event_start"
						id="evetn_start" value=""/>
				<label for="event_end">End Time</lable>
				<input type="text" name="event_end" 
						id="event_end" value=""/>
				<lable for="event_description">Event Description</lable>
				<textarea name="event_description"></textarea>
				<input type="hidden" name="event_id" value="" />
				<input type="hidden" name="token" value="$_SESSION[token]"/>				<input type="hidden" name="action" value="event_edit" />
				<input type="submit" name="event_submit" value="Create a New Event" />
			or <a href="./">cancel</a>
			</fieldset>
		</from>
FORM_MARKUP;
		}
	}
	
	/** 
	 * p144
	 *验证表单，保存/更新活动信息
	 *@return 成功返回TRUE ，失败返回出错信息
	 */
	public function processForm()
	{
			
		/**
		 *若action设置不正确，退出
		 */
		if($_POST['action']!='event_edit')
		{
			return "The method processFrom was accessed incorrectly";
		}

		/**
		 *转义表单提交过来的数据
		 */
		$title = htmlentities($_POST['event_title'],ENT_QUOTES);
		$desc =  htmlentities($_POST['event_description'],ENT_QUOTES);
		$start =  htmlentities($_POST['event_start'],ENT_QUOTES);
		$end =  htmlentities($_POST['event_end'],ENT_QUOTES);


		//var_dump($_POST);
	    //exit();	
		/**
		 *如果提交数据中没有活动ID，就创建一个新的活动
		 */
		//var_dump($_POST['event_id']);
		
		if(empty($_POST['event_id']))
		{
			$sql= "INSERT INTO `events` (`event_title`,`event_desc`,`event_start`,`event_end`) VALUES (:title,:description,:start,:end)";
		}else{	/**
		 *否则就更新这个活动
		 */
				/**
				 *为数据库安全，强制转换ID为整数
				 */
		
		$id = (int)$_POST['event_id'];
		$sql = "UPDATE `events` SET 
				`event_title` =:title,
				`event_start` = :start,
				`event_desc` = :description,
				`event_end` = :end
				WHERE `event_id` = $id";
		}
		//echo $sql;
		//exit;
		/**
		 *绑定参数执行查询
		 */
		try
		{
		//echo $sql;
			$stmt = $this->db->prepare($sql);
		    $stmt->bindParam(":title",$title,PDO::PARAM_STR);
			$stmt->bindParam(":description",$desc,PDO::PARAM_STR);
			$stmt->bindParam(":start",$start,PDO::PARAM_STR);
			$stmt->bindParam(":end",$end,PDO::PARAM_STR);
		
			$stmt->execute();
			$stmt->closeCursor();
			//var_dump($this->db->lastInsertId());
			return TRUE;
		}
		catch(Exception $e)
		{

			return $e->getMessage();
		}
	
		


	}
		
	/**
	 * p157
	 * 确认一个活动是否该被删除并执行
	 *
	 * 在单击删除按钮删除活动时，会生成一个确认的窗口，如果
	 * 用户删除，则从数据库中删除次活动并将用户送回主页
	 * 如果不删除则不操作返回主页
	 *
	 * @param int $id 活动ID
	 * @return 若确认删除可能返回null或异常信息，则返回null
	 */

	public function confirmDelete($id)
	{
//			var_dump($_POST['confirm_delete']);
	
//	var_dump($id);		//exit;
//exit;
			/**
			 *检查是否传入了ID参数
			 */
			if(empty($id)){return NULL;}

			/**
			 *确保这个ID是整数
			 */
			$id= preg_replace('/[^0-9]/','',$id);

			/**
			 *若确认表单且提交有一个正确的记号，检查表单提交数据
			 */

			if(isset($_POST['confirm_delete']) 
					&& $_POST['token']==$_SESSION['token'])
			{
					/**
					 *若用户确认删除，则从数据库中删除次活动
					 */

				
					if($_POST['confirm_delete']=='Yes,Delete It')
					{
							$sql="DELETE FROM `events`
								WHERE `event_id`=:id LIMIT 1";
						
							try
							{
								$stmt =$this->db->prepare($sql);
								$stmt->bindParam(":id",$id,PDO::PARAM_INT);
								$stmt->execute();
								header("Location: ./");
								return;
							}catch(Exception $e){
								return $e->getMessage();	
							}
					}else{//若为确认删除则将用户带往主页
							header("Location:./");
							return;
					}
			}

			/**
			 *若确认表单尚未提交，显示它
			 */
			$event = $this->_loadEventById($id);
			/**
			 *若得到的$event并非对象，则将用户带往主页
			 */
			if(!is_object($event)){header("Location:./");}
					return <<<CONFIRM_DELETE
	<form action="confirmdelete.php" method="post">
		<h2>
			Are you sure you want to delete "$event->title"?
		</h2>
		<p>
			There is <strong>no undo</strong>if you continue.
		</p>
		<p>
			<input type="submit" name="confirm_delete"
						value="Yes,Delete It" />
			<input type="submit" name="confirm_delete"
						value="Nope! Just Kidding!" />
			<input type="hidden" name="event_id"
						value="$event->id" />
			<input type="hidden" name="token"
						value="$_SESSION[token]" />
		</p>
	</form>
CONFIRM_DELETE;

	}


	/**
	 *根据活动ID得到event对象
	 * p130 整理活动数据
	 * @param init $id 活动ID
	 * @return object 活动对象
	 */

	private function _loadEventById($id)
	{

			/**
			 *如果ID为空，返回NULL
			 */
			if(empty($id)){
				return NULL;
			}

			/**
			 *载入活动信息数组
			 */
			$event = $this->_loadEventData($id);

			/**
			 *返回event对象
			 */
			if(isset($event[0]))
			{
					return new Event($event[0]);
			}else{
					return null;
			}
	}

	/**
	 *p148
	 *生成管理连接的HTML 
	 */
	private function _adminGeneralOptions()
	{
		/**
		 *显示管理界面
		 */
			return <<<ADMIN_OPTIONS

		<a href="admin.php" class="admin">+Add a New Event</a>

ADMIN_OPTIONS;
	
	}
	/**
	 *为给定活动id生成修改和删除选项按钮
	 *@param int $id 活动ID
	 *@return string 修改删除选项标记
	 */ 

	private function _adminEntryOptions($id)
	{
		return <<<ADMIN_OPTIONS
<div class="admin-options">
<form action="admin.php" method="post">
<p>
	<input type="submit" name="edit_event"
					value="Edit This Event" />
	<input type="hidden" name="event_id" 
					value="$id" />
</p>
</form>
<form action="confirmdelete.php" method="post">
<p> 
	<input type="submit" name="delete_event" 
						value="Delete This Event" />
	<input type="hidden" name="event_id" 
						value="$id" />
</p>
</form>
</div><!-- end .admin-option-->
ADMIN_OPTIONS;
	}

	public 	function test(){
	//	return	$this->_loadEventData();
	//	return $this->_createEventObj();
	//	return $this->_loadEventById(1);
	//  return $this->displayEvent(1);
	//return $this->displayForm();	
	//return $this->processForm();	
    //return $this->  _adminEntryOptions(1);


	}
}

?>
