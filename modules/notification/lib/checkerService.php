<?php
/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.notification.lib
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: checkerService.php 3366 2012-05-31 06:09:02Z rockyswen@gmail.com $
 */

use Openbizx\Openbizx;
use Openbizx\Object\MetaObject;

class checkerService extends MetaObject
{
	protected $checkerList;
	protected $checkerDO = "notification.do.NotificationCheckerDO";	
	protected $notificationDO = "notification.do.NotificationDO";	
	
	public function __construct(&$xmlArr)
	{
		$this->readMetadata($xmlArr);
	}
	
	public function readMetadata($xmlArr)
	{
		parent::readMetadata($xmlArr);
		$checkers = $xmlArr['PLUGINSERVICE']['CHECKER'];
		$this->_processCheckerList($checkers);
	}
	
	protected function _processCheckerList($checkers)
	{
		if($checkers['ATTRIBUTES'])
		{
			$this->checkerList[] = $checkers["ATTRIBUTES"];
		}
		else{
			foreach($checkers as $checker)
			{
				$this->checkerList[] = $checker["ATTRIBUTES"];
			}	
		}
	}
	

	
	public function checkNotification()
	{
		$checkerDO = Openbizx::getObject($this->checkerDO);
		$checkerRecs = $checkerDO->directfetch();
		$checkerLogList = array();
		foreach($checkerRecs as $checker)
		{
			$checkerLogList[$checker['checker']] = strtotime($checker['last_checktime']);
		}
		foreach($this->checkerList as $checker)
		{
			//test is the checker recently called
			if( $checkerLogList[$checker['NAME']] + (int)$checker['MININTERVAL'] < time() ){
			
				$method_name = $checker['MEHTOD'];
				$obj = Openbizx::getObject($checker['SERVICEOBJ']);
				$notificationList = call_user_func(array($obj,$method_name));
				$this->saveNotificationList($notificationList);				
				
				//update checker log
				$checker_name = $checker['NAME'];
				$time_str = date('Y-m-d H:i:s');
				if(isset($checkerLogList[$checker['NAME']]))
				{
					//update it
					$checkerDO->updateRecords("[last_checktime]='$time_str'","[checker]='$checker_name'");
				}
				else
				{
					//insert it
					$checkerLogArr=array(
						"checker" => $checker_name,
						"last_checktime"=>$time_str
					);
					$checkerDO->insertRecord($checkerLogArr);
				}
			}
		}
	}
	
	public function saveNotificationList($notificationList)
	{
		$notiDO = Openbizx::getObject($this->notificationDO);
		$counter = 0;
		foreach ($notificationList as $notiRec)
		{		
			if(!$notiRec['type'])
			{
				continue;
			}
			$notiArr = array(				
				"type"=>$notiRec['type'],
				"subject"=>$notiRec['subject'],
				"message"=>$notiRec['message'],
				"goto_url"=>$notiRec['goto_url'],
				"read_access"=>$notiRec['read_access'],
				"update_access"=>$notiRec['update_access'],
				"read_state"=>0,
			);	
			$result = $notiDO->insertRecord($notiArr);
			if($result){
				$counter++;
			}
		}
		return $counter;
	}
}
