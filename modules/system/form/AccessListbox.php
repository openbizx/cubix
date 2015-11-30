<?PHP
/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.system.form
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: AccessListbox.php 3812 2012-08-05 07:14:11Z rockyswen@gmail.com $
 */

use Openbizx\Easy\Element\Listbox;

class AccessListbox extends Listbox
{
    public function render()
    {
        // change name as name_actionid
        $elem = $this->getFormObj()->getElement('fld_Id');
        $aclActionId = $elem->getValue();
        
        $fromList = array();
        $this->getFromList($fromList);
        $valueArray = explode(',', $this->value);
        $disabledStr = ($this->getEnabled() == "N") ? "DISABLED=\"true\"" : "";
        $style = $this->getStyle();
        $func = $this->getFunction();

        //$sHTML = "<SELECT NAME=\"" . $this->objectName . "[]\" ID=\"" . $this->objectName ."\" $disabledStr $this->htmlAttr $style $func>";
        $sHTML = "<SELECT NAME=\"" . $this->objectName . "[]\" ID=\"" . $this->objectName ."\" $disabledStr $this->htmlAttr $style $func>";

        if ($this->blankOption) // ADD a blank option
        {
            $entry = explode(",",$this->blankOption);
            $text = $entry[0];
            $value = ($entry[1]!= "") ? $entry[1] : null;
            $entryList = array(array("val" => $value, "txt" => $text ));
            $fromList = array_merge($entryList, $fromList);
        }

        foreach ($fromList as $option)
        {
            $test = array_search($option['val'], $valueArray);
            if ($test === false)
            {
                $selectedStr = '';
            }
            else
            {
                $selectedStr = "SELECTED";
            }
            $sHTML .= "<OPTION VALUE=\"" . $option['val'] . "\" $selectedStr>" . $option['txt'] . "</OPTION>";
        }
        $sHTML .= "</SELECT>";
        $sHTML .= "<input type='hidden' name='action_id[]' value='$aclActionId'/>";
        
        return $sHTML;
    }
}


