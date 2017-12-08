<?php

use SilverShop\Core\ShopCurrency;
use SilverShop\Core\CheckoutPage;
use SilverShop\Core\SteppedCheckout;

define('SHOP_DIR',basename(__DIR__));
define('SHOP_PATH',BASE_PATH.DIRECTORY_SEPARATOR.SHOP_DIR);

Object::useCustomClass('Currency',ShopCurrency::class, true);

if($checkoutsteps = CheckoutPage::config()->steps){
	SteppedCheckout::setupSteps($checkoutsteps);
}
