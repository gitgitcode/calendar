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
		$html .="\n\t<ul calss=\"weekdays\">".$labels."\n\t</ul>";
		
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
		 *为起始日之前的几天添加calss fill
		 */	
			$class = $i<= $this->_startDay?"fill":null;
			/**
			 *如果当前处理日期是今天，则为它添加class today
			 */

			if($c+1==$t && $m==$this->_m && $y==$this->_y){
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
			 echo $c.'<br/>';
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
		/**
		 *返回用于输出的html标记
		 */
		return $html;
	}	



	public 	function test(){
	//	return	$this->_loadEventData();
	return $this->_createEventObj();
	}
}

?>









