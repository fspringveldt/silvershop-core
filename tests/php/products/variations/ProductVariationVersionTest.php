<?php

namespace SilverShop\Core\Tests;

use SapphireTest;
use SilverShop\Core\Product;
use SilverShop\Core\ProductVariation;
use SilverShop\Core\ProductAttributeType;



/**
 * Test product variation capabilities.
 *
 * @link       ProductVariation
 * @link       ProductVariationDecorator
 * @package    shop
 * @subpackage tests
 */
class ProductVariationVersionTest extends SapphireTest
{
    public static $fixture_file   = 'silvershop/tests/fixtures/variations.yml';
    public static $disable_theme  = true;
    public static $use_draft_site = true;

    public function setUp()
    {
        parent::setUp();
        $this->ball = $this->objFromFixture(Product::class, "ball");
        $this->mp3player = $this->objFromFixture(Product::class, "mp3player");
        $this->redlarge = $this->objFromFixture(ProductVariation::class, "redlarge");
    }

    public function testVariationsPersistOnUnpublish()
    {
        $color = $this->objFromFixture(ProductAttributeType::class, "color");
        $values = array('Black', 'Blue');
        $this->mp3player->generateVariationsFromAttributes($color, $values);
        $this->mp3player->publish('Stage', 'Live');

        $this->mp3player->publish('Stage', 'Stage');

        $variations = $this->mp3player->Variations();
        $this->assertEquals($variations->Count(), 2, "two variations created and persist after product unpublished");
    }
}
