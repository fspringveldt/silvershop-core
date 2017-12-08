<?php

namespace SilverShop\Core;

use Config;
use Member;
use Controller;
use FieldList;
use FormAction;
use SilverShop\Core\CheckoutStep_ContactDetails;



class CheckoutStep_ContactDetails extends CheckoutStep
{
    private static $allowed_actions = array(
        'contactdetails',
        'ContactDetailsForm',
    );

    public function contactdetails()
    {
        $form = $this->ContactDetailsForm();
        if (ShoppingCart::curr()
            && Config::inst()->get(CheckoutStep_ContactDetails::class, "skip_if_logged_in")
        ) {
            if (Member::currentUser()) {
                if(!$form->getValidator()->validate()) {
                    return Controller::curr()->redirect($this->NextStepLink());
                } else {
                    $form->clearMessage();
                }
            }
        }

        return array(
            'OrderForm' => $form,
        );
    }

    public function ContactDetailsForm()
    {
        $cart = ShoppingCart::curr();
        if (!$cart) {
            return false;
        }
        $config = new CheckoutComponentConfig(ShoppingCart::curr());
        $config->addComponent(CustomerDetailsCheckoutComponent::create());
        $form = CheckoutForm::create($this->owner, 'ContactDetailsForm', $config);
        $form->setRedirectLink($this->NextStepLink());
        $form->setActions(
            FieldList::create(
                FormAction::create("checkoutSubmit", _t('SilverShop\\Core\\CheckoutStep.Continue', "Continue"))
            )
        );
        $this->owner->extend('updateContactDetailsForm', $form);

        return $form;
    }
}
