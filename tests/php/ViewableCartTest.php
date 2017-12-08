<?php

namespace SilverShop\Core\Tests;

use SapphireTest;

use Page_Controller;
use SilverShop\Core\ShoppingCart;
use SilverShop\Core\Product;
use SilverShop\Core\Order;



class ViewableCartTest extends SapphireTest
{
    public static $fixture_file  = 'silvershop/tests/fixtures/shop.yml';
    public static $disable_theme = true;

    public function setUpOnce()
    {
        parent::setUpOnce();
        // clear session
        ShoppingCart::singleton()->clear();
    }

    function setUp()
    {
        parent::setUp();
        ShopTest::setConfiguration();
        $this->objFromFixture(Product::class, "socks")->publish("Stage", "Live");
    }

    function testCart()
    {
        $cart = $this->objFromFixture(Order::class, "cart");
        ShoppingCart::singleton()->setCurrent($cart);
        $page = new Page_Controller();
        $this->assertEquals("$8.00", (string)$page->renderWith("CartTestTemplate"));
    }
}
