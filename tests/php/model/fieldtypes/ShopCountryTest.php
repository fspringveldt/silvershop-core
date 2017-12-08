<?php

namespace SilverShop\Core\Tests;

use SapphireTest;

use SilverShop\Core\ShopCountry;



class ShopCountryTest extends SapphireTest
{
    public function testField()
    {
        $field = new ShopCountry("Country");
        $field->setValue("ABC");
        $this->assertEquals("ABC", $field->forTemplate());
        $field->setValue("NZ");
        $this->assertEquals("New Zealand", $field->forTemplate());
    }
}
