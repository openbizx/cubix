<?php

use Openbizx\Easy\EasyForm;

include_once (Openbizx::$app->getModulePath()."/common/lib/Pivot.php");

class PivotTableForm extends EasyForm
{
    protected $pivotConfig, $pivotMeta;
    protected $queryLimit = 1000;
	protected $queryId;
    
    function __construct(&$xmlArr)
	{
		parent::__construct($xmlArr);
		
		$this->initPivotConfig();
	}
    
    protected function initPivotConfig()
    {
        $this->pivotConfig['columns'] = array();
		$this->pivotConfig['rows'] = array();
		$this->pivotConfig['datas'] = array();
		foreach ($this->dataPanel as $elem) {
			if ($elem->pivotType == 'Column') {
				$this->pivotConfig['columns'][$elem->fieldName] = $elem->label;
			}
			else if ($elem->pivotType == 'Row') {
				$this->pivotConfig['rows'][$elem->fieldName] = $elem->label;
			}
			else if ($elem->pivotType == 'Data') {
				$this->pivotConfig['datas'][$elem->fieldName] = $elem->label;
			}
		}
    }
    
    protected function getPivotData() 
	{
		$recordset = parent::fetchDataSet();
		
		// convert the normal record set to pivot data array
		$data = Pivot::factory($recordset)
				->pivotOn(array_keys($this->pivotConfig['rows']))
				->addColumn(array_keys($this->pivotConfig['columns']), array_keys($this->pivotConfig['datas']))
				->fullTotal()
				->pivotTotal()
				->fetch(1);
		//print_r($data); exit;
		return $data;
	}
    
    public function fetchDataSet()
    {
    }
	
	public function outputAttrs()
    { 
        $output['name'] = $this->objectName;
        $output['title'] = $this->title;
        $output['description'] = str_replace('\n', "<br />", $this->objectDescription);
        $output['meta'] = $this->pivotConfig;
		$data = $this->getPivotData();
		$output['headers'] = $data['splits'];
        $output['data'] = $data['data'];
        return $output;
    }
}
