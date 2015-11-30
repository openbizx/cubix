<?php

use Openbizx\Openbizx;

require_once Openbizx::$app->getModulePath().'/websvc/lib/WebsvcService.php';
class userService extends  WebsvcService
{
	public function getStatus()
	{
		$result = array();
		$userId = Openbizx::$app->getUserProfile("Id");
		if($userId)
		{
			$result['login_status'] = 1;
			$result['display_name'] = Openbizx::$app->getUserProfile("profile_display_name");
			$result['email'] 		= Openbizx::$app->getUserProfile("email");
		}
		else
		{
			$result['login_status'] = 0;			
		}
		return $result;
	}
}
