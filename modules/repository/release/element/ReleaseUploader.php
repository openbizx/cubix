<?php

/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.repository.release.element
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: ReleaseUploader.php 3369 2012-05-31 06:13:56Z rockyswen@gmail.com $
 */
use Openbizx\Easy\Element\InputText;

class ReleaseUploader extends InputText
{

    public function render()
    {
        $this->cssClass = null;
        $this->cssErrorClass = null;
        $this->cssHoverClass = null;

        $sHTML = "
        <INPUT NAME=\"" . $this->objectName . "\" ID=\"" . $this->objectName . "\" VALUE=\"" . $value . "\"  />
        
        ";

        $formObj = $this->getFormObj();
        $formName = $formObj->objectName;

        $sHTML .= "
        <script>
		  \$j('#" . $this->objectName . "').uploadify({
		    'uploader'  : '" . OPENBIZ_JS_URL . "/uploadify/uploadify.swf',		    
		    'cancelImg' : '" . OPENBIZ_JS_URL . "/uploadify/cancel.png',
		    'script'    : '" . OPENBIZ_APP_URL . "/bin/controller.php',
		    'scriptData': { 'F':'RPCInvoke',
 							'P0':'[repository.release.widget.ReleaseNewForm]',
 							'P1':'[uploadFile]',
 							'__this':'btn_upload_file:upload_onclick',
 							'_selectedId':'',
 							'session_id':'" . session_id() . "',
 							'cubi_sess_id':'" . session_id() . "'
 							},
		    'folder'    : '" . OPENBIZ_JS_URL . "/uploadify/test',
		    'displayData' : true,
		    'multi'      : false,
		   
		    'auto'      : false,
		    'onComplete' : function(event, ID, fileObj, response, data){							
							Openbizx.CallFunction('$formName.fileUploadComplete('+response+')');
 						},
 			'onAllComplete': function(event,data){ 			 							
 							Openbizx.CallFunction('$formName.allUploadComplete()');
 						}
		  });
		        
        </script>       
        ";
        return $sHTML;
    }

}
