<?php

namespace SilverShop\Core;

use ModelAdmin;
use SilverShop\Core\Zone;



class ZoneAdmin extends ModelAdmin
{
    private static $menu_title     = "Zones";

    private static $url_segment    = "zones";

    private static $menu_icon      = 'silvershop/images/icons/local-admin.png';

    private static $menu_priority  = 2;

    private static $managed_models = array(
        Zone::class,
    );
}
