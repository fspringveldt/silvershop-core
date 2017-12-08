<?php

namespace SilverShop\Core;

use RequiredFields;


/**
 * @package shop
 */
class VariationFormValidator extends RequiredFields
{
    public function php($data)
    {
        $valid = parent::php($data);

        if ($valid && !$this->form->getBuyable($_POST)) {
            $this->validationError(
                "",
                _t('SilverShop\\Core\\VariationForm.ProductNotAvailable', "This product is not available with the selected options.")
            );

            $valid = false;
        }

        return $valid;
    }
}
