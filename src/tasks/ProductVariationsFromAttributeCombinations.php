<?php

namespace SilverShop\Core;

use CliController;
use DataObject;
use SilverShop\Core\Product;



/**
 *
 * @subpackage tasks
 */
class ProductVariationsFromAttributeCombinations extends CliController
{
    public function process()
    {

        $products = DataObject::get(Product::class);
        if (!$products) {
            return;
        }

        foreach ($products as $product) {
            $product->generateVariationsFromAttributes();
        }
    }
}
