<?PHP

use Openbizx\Easy\Element\ColumnText;

/**
 * PivotColumnText class is element for pivot column,
 * show text on data list
 *
 * @package openbiz.bin.easy.element
 * @author Rocky Swen
 * @copyright Copyright (c) 2005-2009
 * @access public
 */
class PivotColumnText extends ColumnText
{
    public $pivotType;

    /**
     * Read array meta data, and store to meta object
     *
     * @param array $xmlArr
     * @return void
     */
    protected function readMetaData(&$xmlArr)
    {
        parent::readMetaData($xmlArr);
        $this->pivotType = isset($xmlArr["ATTRIBUTES"]["PIVOTTYPE"]) ? $xmlArr["ATTRIBUTES"]["PIVOTTYPE"] : null;     
	}
}
