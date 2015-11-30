<?php

/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.location.element
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: LocationPreview.php 3362 2012-05-31 06:03:29Z rockyswen@gmail.com $
 */
use Openbizx\Easy\Element\Element;

class LocationPreview extends Element
{

    public function render()
    {
        $record = $this->getFormObj()->getActiveRecord();
        $latitude = $record['latitude'];
        $longtitude = $record['longtitude'];
        $title = $record['title'];
        $imgsrc = "http://maps.googleapis.com/maps/api/staticmap?center=$latitude,$longtitude&zoom=10&size=380x240&maptype=roadmap
&markers=color:red%7Clabel:$title%7C$latitude,$longtitude&sensor=false
		";
        $HTML = "<a target=\"_blank\" href=\"$imgsrc\"><img width=\"380\" height=\"240\" src=\"$imgsrc\" border=\"0\" /></a>";
        return $HTML;
    }

}
