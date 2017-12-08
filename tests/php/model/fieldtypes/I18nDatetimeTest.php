<?php

namespace SilverShop\Core\Tests;

use SapphireTest;

use SilverShop\Core\I18nDatetime;



class I18nDatetimeTest extends SapphireTest
{
    public function testField()
    {

        $field = new I18nDatetime();
        $field->setValue('2012-11-21 11:54:13');

        $field->Nice();
        $field->NiceDate();
        $field->Nice24();

        $this->markTestIncomplete('assertions!');
    }
}
