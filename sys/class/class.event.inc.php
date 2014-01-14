<?php

/**
 *保存活动信息
 *PHP 5.5
 *@author chao
 *@copyright 2014
 *@license
 */

class Event{
/**
 *后动ID
 *@var int
 */
public $id;
/**
 *活动标题
 *@var string
 */
public $title;
/**
 *活动描述
 * @var string
 */
public $description;

 /**
 *活动起始时间
 * @var string
 */
public $start;

/**
 *活动结束时间
 * @var string
 */
public $end;
/**
 *接受一个活动的数据并存储该数据
 * @param array $evnet 保存数据的关联数组
 * @return void
 */ 
public function __construct($event){
		if(is_array($event)){
		$this->id =$event['event_id'];
		$this->title =$event['event_title'];
		$this->description =$event['event_desc'];
		$this->start =$event['event_start'];
		$this->end =$event['event_end'];
			
		}else{
		throw new Exception("No event data was supplied");	
		}
}
}
