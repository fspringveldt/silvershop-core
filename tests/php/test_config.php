<?php

namespace SilverShop\Core\Tests;

use Object;
use Config;
use SilverShop\Core\I18nDatetime;
use SilverShop\Core\Order;
use SilverShop\Core\ProductCatalogAdmin;
use SilverShop\Core\ProductCategory;
use SilverShop\Core\ShopCurrency;
use SilverShop\Core\Product;
use SilverShop\Core\ProductVariation;
use SilverShop\Core\ProductAttributeType;
use SilverShop\Core\ShoppingCart_Controller;
use SilverShop\Core\FlatTaxModifier;
use SilverShop\Core\GlobalTaxModifier;
use SilverShop\Core\SimpleShippingModifier;
use SilverShop\Core\ShopConfig;
use SilverShop\Core\SteppedCheckout;
use SilverShop\Core\Address;
use SilverShop\Core\OrderActionsForm;
use SilverShop\Core\OrderProcessor;
use SilverShop\Core\CheckoutComponentConfig;



Object::useCustomClass('SS_Datetime', I18nDatetime::class, true);

/// Reset to all default configuration settings.

$cfg = Config::inst();

//remove array configs (these get merged, rater than replaced)

$cfg->remove("Payment", "allowed_gateways");
$cfg->remove(Order::class, "modifiers");
$cfg->remove(ProductCatalogAdmin::class, "managed_models");
$cfg->remove(ProductCategory::class, "sort_options");

// non-ecommerce
$cfg->update('Member', 'unique_identifier_field', 'Email');
$cfg->update('Email', 'admin_email', 'shopadmin@example.com');
$cfg->update(
    'Payment',
    'allowed_gateways',
    array(
        'Dummy',
        'Manual',
    )
);

// i18n
$cfg->update(ShopCurrency::class, 'decimal_delimiter', '.');
$cfg->update(ShopCurrency::class, 'thousand_delimiter', '');
$cfg->update(ShopCurrency::class, 'negative_value_format', '-%s');

// products
$cfg->update(Product::class, 'global_allow_purchase', true);
$cfg->update(
    ProductCatalogAdmin::class,
    'managed_models',
    array(Product::class, ProductCategory::class, ProductVariation::class, ProductAttributeType::class)
);
$cfg->update('Product_image', 'thumbnail_width', 140);
$cfg->update('Product_image', 'thumbnail_height', 100);
$cfg->update('Product_image', 'large_image_width', 200);
$cfg->update(ProductCategory::class, 'include_child_groups', true);
$cfg->update(ProductCategory::class, 'page_length', 10);
$cfg->update(ProductCategory::class, 'must_have_price', true);
$cfg->update(ProductCategory::class, 'sort_options', array('Title' => 'Alphabetical', 'Price' => 'Lowest Price'));

// cart, order
$cfg->update(Order::class, 'modifiers', array());
$cfg->update(Order::class, 'cancel_before_payment', true);
$cfg->update(Order::class, 'cancel_before_processing', false);
$cfg->update(Order::class, 'cancel_before_sending', false);
$cfg->update(Order::class, 'cancel_after_sending', false);
$cfg->update(ShoppingCart_Controller::class, 'direct_to_cart_page', false);

//modifiers
$cfg->update(FlatTaxModifier::class, 'name', 'NZD');
$cfg->update(FlatTaxModifier::class, 'rate', 0.15);
$cfg->update(FlatTaxModifier::class, 'exclusive', true);

$cfg->update(
    GlobalTaxModifier::class,
    'country_rates',
    array(
        "NZ" => array("rate" => 0.15, "name" => "GST", "exclusive" => false),
    )
);

$cfg->update(SimpleShippingModifier::class, 'default_charge', 10);
$cfg->update(SimpleShippingModifier::class, 'charges_for_countries', array('US' => 10, 'NZ' => 5));

// checkout
$cfg->update(ShopConfig::class, 'email_from', null);
$cfg->update(ShopConfig::class, 'base_currency', 'NZD');
$cfg->update(SteppedCheckout::class, 'first_step', null);
$cfg->update(
    Address::class,
    'requiredfields',
    array(
        Address::class,
        'City',
        'State',
        'Country',
    )
);
$cfg->update(OrderActionsForm::class, 'set_allow_cancelling', false);
$cfg->update(OrderActionsForm::class, 'set_allow_paying', false);

// injector resets
$classes = array(
    OrderProcessor::class,
    CheckoutComponentConfig::class,
    "Security",
    "PurchaseService",
);
foreach ($classes as $class) {
    $cfg->update('Injector', $class, array("class" => $class));
}
