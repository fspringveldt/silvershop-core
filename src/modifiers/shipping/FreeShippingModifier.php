<?php

namespace SilverShop\Core;




class FreeShippingModifier extends ShippingModifier
{
    /**
     * Calculate whether the current order is eligable for free shipping
     */
    public function eligable()
    {
    }

    public function TableValue()
    {
        return _t("SilverShop\\Core\\FreeShippingModifier.Free", "FREE");
    }
}
