<?php

/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.help.form
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: HelpCategoryForm.php 3345 2012-05-31 05:04:56Z rockyswen@gmail.com $
 */

use Openbizx\Openbizx;
use Openbizx\Easy\EasyFormTree;

class HelpCategoryForm extends EasyFormTree
{

    protected $categoryMappingDO = "help.do.HelpCategoryMappingDO";

    public function UpdateRecord()
    {
        $result = parent::UpdateRecord();
        $mappingObj = Openbizx::getObject($this->categoryMappingDO, 1);
        $Id = $this->recordId;
        $mappingObj->deleteRecords("[cat_id]='$Id'");
        return $result;
    }

    protected function validateForm($cleanError = true)
    {

        $result = parent::validateForm($cleanError);

        if ($result) {
            $parentId = $this->dataPanel->get("fld_parent_id")->value;
            $currentId = $this->dataPanel->get("fld_Id")->value;
            if ($parentId == $currentId && strtoupper($this->formType) == 'EDIT') {
                $errorMessage = $this->getMessage("FORM_PARENT_SHOULD_NOT_SAME_AS_ITSELF");
                $this->validateErrors[$element->objectName] = $errorMessage;
            }

            if (!$this->hasRootExcept($currentId) && strtoupper($this->formType) == 'EDIT') {
                $errorMessage = $this->getMessage("FORM_ITS_LAST_ROOT_CATEGORY");
                $this->validateErrors[$element->objectName] = $errorMessage;
            }

            /* todo:
              if ($this->isMovedToChild($parentId,$currentId) && strtoupper($this->formType)=='EDIT')
              {
              $errorMessage = $this->getMessage("FORM_CANNOT_MOVE_ITS_CHILD");
              $this->validateErrors[$element->objectName] = $errorMessage;
              }
             */

            if (count($this->validateErrors) > 0) {
                throw new Openbizx\Validation\Exception($this->validateErrors);
                return false;
            }
        }
        return $result;
    }

    protected function hasRootExcept($currentId)
    {
        $rs = $this->getDataObj()->fetchOne("[PId]=0 AND [Id]!='" . (int) $currentId . "'");
        if ($rs) {
            return true;
        } else {
            return false;
        }
    }

}

