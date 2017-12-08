<?php

namespace SilverShop\Core\Tests;

use SapphireTest;


use SilverShop\Core\Product;
use SilverShop\Core\Product_Controller;
use SilverShop\Core\AddProductForm;



class AddProductFormTest extends SapphireTest
{
    public static $fixture_file = "silvershop/tests/fixtures/shop.yml";

    public function testForm()
    {

        $controller = new Product_Controller($this->objFromFixture(Product::class, "socks"));
        $form = new AddProductForm($controller);
        $form->setMaximumQuantity(10);

        $this->markTestIncomplete("test can't go over max quantity");

        $data = array(
            'Quantity' => 4,
        );
        $form->addtocart($data, $form);

        $this->markTestIncomplete('check quantity');
    }
}
