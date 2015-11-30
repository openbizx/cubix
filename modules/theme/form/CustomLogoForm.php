<?php

use Openbizx\Easy\EasyForm;

class CustomLogoForm extends EasyForm
{

    public function UpdateLogo()
    {
        $recArr = $this->readInputRecord();
        $imgfile = $recArr['custom_logo'];
        $imgfile = OPENBIZ_APP_PATH . DIRECTORY_SEPARATOR . $imgfile;

        $logofile = OPENBIZ_APP_PATH . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'cubi_logo_large.png';
        @copy($imgfile, $logofile);

        $this->processPostAction();
    }

    public function Restore()
    {
        $logofile = OPENBIZ_APP_PATH . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'cubi_logo_large.png';
        $default_logofile = OPENBIZ_APP_PATH . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'cubi_logo_large_default.png';

        @copy($default_logofile, $logofile);
        $this->processPostAction();
    }

}
