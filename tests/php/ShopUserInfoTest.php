<?php

namespace SilverShop\Core\Tests;

use SapphireTest;

use SilverShop\Core\ShopUserInfo;



class ShopUserInfoTest extends SapphireTest
{
    public function testSetLocation()
    {

        ShopUserInfo::singleton()->setLocation(
            array(
                'Country' => 'NZ',
                'State'   => 'Wellington',
                'City'    => 'Newton',
            )
        );

        $location = ShopUserInfo::singleton()->getAddress();

        $this->assertEquals($location->Country, 'NZ');
        $this->assertEquals($location->State, 'Wellington');
        $this->assertEquals($location->City, 'Newton');
    }
}
