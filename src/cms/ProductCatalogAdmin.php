<?php

namespace SilverShop\Core;

use ModelAdmin;
use SilverShop\Core\Product;
use SilverShop\Core\ProductCategory;
use SilverShop\Core\ProductAttributeType;
use SilverShop\Core\ProductBulkLoader;



/**
 * Product Catalog Admin
 *
 * @package    shop
 * @subpackage cms
 **/
class ProductCatalogAdmin extends ModelAdmin
{
    private static $url_segment     = 'catalog';

    private static $menu_title      = 'Catalog';

    private static $menu_priority   = 5;

    private static $menu_icon       = 'silvershop/images/icons/catalog-admin.png';

    private static $managed_models  = array(
        Product::class,
        ProductCategory::class,
        ProductAttributeType::class,
    );

    private static $model_importers = array(
        "Product" => ProductBulkLoader::class,
    );
}
