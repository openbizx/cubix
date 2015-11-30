<?php 

use Openbizx\Easy\WebPage;

class LoaderNotInstalledView extends WebPage
{
	public function render()
	{
		if(!extension_loaded('ionCube Loader'))
		{
			$result = parent::render();
			return $result;
		}else{
			header("Location: ".OPENBIZ_APP_INDEX_URL);
			exit;
		}
	}
	
}
