<?php

namespace SilverShop\Core;

use Page;
use Controller;
use FieldList;
use DropdownField;
use TreeDropdownField;
use DB;
use Page_Controller;
use SilverShop\Core\CheckoutPage;
use SilverShop\Core\CartForm;



/**
 * View and edit the cart in a full page.
 * Visitor can continue shopping, or proceed to checkout.
 */
class CartPage extends Page
{
    private static $has_one = array(
        'CheckoutPage' => CheckoutPage::class,
        'ContinuePage' => 'SiteTree',
    );

    private static $icon    = 'silvershop/images/icons/cart';

    /**
     * Returns the link to the checkout page on this site
     *
     * @param boolean $urlSegment If set to TRUE, only returns the URLSegment field
     *
     * @return string Link to checkout page
     */
    public static function find_link($urlSegment = false, $action = false, $id = false)
    {
        $base = CartPage_Controller::config()->url_segment;
        if ($page = self::get()->first()) {
            $base = $page->Link();
        }
        return Controller::join_links($base, $action, $id);
    }

    public function getCMSFields()
    {
        $this->beforeUpdateCMSFields(
            function (FieldList $fields) {
                if ($checkouts = CheckoutPage::get()) {
                    $fields->addFieldToTab(
                        'Root.Links',
                        DropdownField::create(
                            'CheckoutPageID',
                            _t('SilverShop\\Core\\CartPage.has_one_CheckoutPage', 'Checkout Page'),
                            $checkouts->map("ID", "Title")
                        )
                    );
                }

                $fields->addFieldToTab(
                    'Root.Links',
                    TreeDropdownField::create(
                        'ContinuePageID',
                        _t('SilverShop\\Core\\CartPage.has_one_ContinuePage', 'Continue Shopping Page'),
                        'SiteTree'
                    )
                );
            }
        );

        return parent::getCMSFields();
    }

    /**
     * This module always requires a page model.
     */
    public function requireDefaultRecords()
    {
        parent::requireDefaultRecords();
        if (!self::get()->exists() && $this->config()->create_default_pages) {
            $page = self::create(
                array(
                    'Title'       => 'Shopping Cart',
                    'URLSegment'  => CartPage_Controller::config()->url_segment,
                    'ShowInMenus' => 0,
                )
            );
            $page->write();
            $page->publish('Stage', 'Live');
            $page->flushCache();
            DB::alteration_message('Cart page created', 'created');
        }
    }
}

class CartPage_Controller extends Page_Controller
{
    private static $url_segment     = 'cart';

    private static $allowed_actions = array(
        CartForm::class,
        "updatecart",
    );

    /**
     * Display a title if there is no model, or no title.
     */
    public function Title()
    {
        if ($this->Title) {
            return $this->Title;
        }
        return _t('SilverShop\\Core\\CartPage.DefaultTitle', "Shopping Cart");
    }

    /**
     * A form for updating cart items
     */
    public function CartForm()
    {
        $cart = $this->Cart();
        if (!$cart) {
            return false;
        }
        $form = CartForm::create($this, CartForm::class, $cart);

        $this->extend('updateCartForm', $form);

        return $form;
    }
}