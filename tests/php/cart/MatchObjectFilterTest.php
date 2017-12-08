<?php

namespace SilverShop\Core\Tests;

use SapphireTest;

use SilverShop\Core\Product_OrderItem;
use SilverShop\Core\Product;
use SilverShop\Core\MatchObjectFilter;



class MatchObjectFilterTest extends SapphireTest
{
    public function testRelationId()
    {
        // Tests that an ID is automatically added to any relation fields in the DataObject's has_one.
        $filter = new MatchObjectFilter(Product_OrderItem::class, array('ProductID' => 5), array(Product::class));
        $this->assertEquals($filter->getFilter(), array('"ProductID" = \'5\''), 'ID was added to filter');
    }

    public function testMissingValues()
    {
        // Tests that missing values are included in the filter as IS NULL or = 0
        // Missing value for a has_one relationship field.
        $filter = new MatchObjectFilter(Product_OrderItem::class, array(), array(Product::class));
        $this->assertEquals(
            $filter->getFilter(),
            array('("ProductID" = 0 OR "ProductID" IS NULL)'),
            'missing ID value became IS NULL or = 0'
        );
        // Missing value for a db field.
        $filter = new MatchObjectFilter(Product_OrderItem::class, array(), array('ProductVersion'));
        $this->assertEquals(
            $filter->getFilter(),
            array('"ProductVersion" IS NULL'),
            'missing DB value became IS NULL or = 0'
        );
    }
}
