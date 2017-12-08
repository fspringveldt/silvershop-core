<?php

namespace SilverShop\Core;
use SilverShop\Core\CanBeFreeCurrency;





/**
 * Pickup the order from the store.
 *
 * @package    shop
 * @subpackage shipping
 */
class PickupShippingModifier extends ShippingModifier
{
    private static $defaults      = array(
        'Type' => 'Ignored',
    );

    private static $casting       = array(
        'TableValue' => CanBeFreeCurrency::class,
    );

    private static $singular_name = "Pick Up Shipping";
}
