<?php

namespace SilverShop\Core\Tests;

use SapphireTest;

use SilverShop\Core\ShopTools;



class ShopToolsTest extends SapphireTest
{
    public function setUp()
    {
        parent::setUp();
        ShopTest::setConfiguration();
    }

    public function testPriceForDisplay()
    {
        $dp = ShopTools::price_for_display(12345.67);
        $dp->setCurrency("NZD");
        $dp->setLocale("en_NZ");
        $this->assertEquals($dp->Nice(), "$12,345.67");
    }
}
