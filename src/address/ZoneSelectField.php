<?php

namespace SilverShop\Core;

use DropdownField;
use DataObject;
use SilverShop\Core\Zone;



class ZoneSelectField extends DropdownField
{
    public function getSource()
    {
        $zones = DataObject::get(Zone::class);
        if ($zones && $zones->exists()) {
            return array("" => $this->emptyString) + $zones->map('ID', 'Name');
        }
        return array();
    }
}
