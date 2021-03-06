<?php

namespace SilverShop\Core;

use Currency;


/**
 * Allows casting some template values to show "FREE" instead of $0.00.
 */
class CanBeFreeCurrency extends Currency
{
    public function Nice()
    {
        if ($this->value == 0) {
            return _t("SilverShop\\Core\\ShopCurrency.Free", "<span class=\"free\">FREE</span>");
        }
        return parent::Nice();
    }
}
