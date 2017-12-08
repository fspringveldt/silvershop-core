<?php

namespace SilverShop\Core;

use Page;
use FieldList;
use TextField;
use DropdownField;
use ListBoxField;
use CheckboxField;
use UploadField;
use SiteConfig;
use Director;
use Page_Controller;
use Versioned;
use SilverShop\Core\ProductCategory;
use SilverShop\Core\Product;
use SilverShop\Core\Product_OrderItem;
use SilverShop\Core\ProductVariationsExtension;
use SilverShop\Core\AddProductForm;



/**
 * This is a standard Product page-type with fields like
 * Price, Weight, Model and basic management of
 * groups.
 *
 * It also has an associated Product_OrderItem class,
 * an extension of OrderItem, which is the mechanism
 * that links this page type class to the rest of the
 * eCommerce platform. This means you can add an instance
 * of this page type to the shopping cart.
 *
 * @package shop
 */
class Product extends Page implements Buyable
{
    private static $db                     = array(
        'InternalItemID' => 'Varchar(30)', //ie SKU, ProductID etc (internal / existing recognition of product)
        'Model'          => 'Varchar(30)',

        'CostPrice' => 'Currency(19,4)', // Wholesale cost of the product to the merchant
        'BasePrice' => 'Currency(19,4)', // Base retail price the item is marked at.

        //physical properties
        'Weight'    => 'Decimal(12,5)',
        'Height'    => 'Decimal(12,5)',
        'Width'     => 'Decimal(12,5)',
        'Depth'     => 'Decimal(12,5)',

        'Featured'      => 'Boolean',
        'AllowPurchase' => 'Boolean',

        'Popularity' => 'Float' //storage for ClaculateProductPopularity task
    );

    private static $has_one                = array(
        'Image' => 'Image',
    );

    private static $many_many              = array(
        'ProductCategories' => ProductCategory::class,
    );

    private static $defaults               = array(
        'AllowPurchase' => true,
        'ShowInMenus'   => false,
    );

    private static $casting                = array(
        'Price' => 'Currency',
    );

    private static $summary_fields         = array(
        'InternalItemID',
        'Title',
        'BasePrice.NiceOrEmpty',
        'canPurchase',
    );

    private static $searchable_fields      = array(
        'InternalItemID',
        'Title' => array("title" => 'Title'),
        'Featured',
    );

    private static $field_labels           = array(
        'InternalItemID'        => 'SKU',
        'Title'                 => 'Title',
        'BasePrice'             => 'Price',
        'BasePrice.NiceOrEmpty' => 'Price',
        'canPurchase'           => 'Purchasable',
    );

    private static $singular_name          = Product::class;

    private static $plural_name            = "Products";

    private static $icon                   = 'silvershop/images/icons/package';

    private static $default_parent         = ProductCategory::class;

    private static $default_sort           = '"Title" ASC';

    private static $global_allow_purchase  = true;

    private static $allow_zero_price       = false;

    private static $order_item             = Product_OrderItem::class;

    private static $min_opengraph_img_size = 0;

    // Physical Measurement
    private static $weight_unit = "kg";

    private static $length_unit = "cm";

    private static $indexes     = array(
        'Featured'       => true,
        'AllowPurchase'  => true,
        'InternalItemID' => true,
    );

    /**
     * Add product fields to CMS
     *
     * @return FieldList updated field list
     */
    public function getCMSFields()
    {
        $self = $this;

        $this->beforeUpdateCMSFields(
            function (FieldList $fields) use ($self) {
                $fields->fieldByName('Root.Main.Title')
                    ->setTitle(_t('SilverShop\\Core\\Product.PageTitle', 'Product Title'));

                $fields->addFieldsToTab(
                    'Root.Main', [
                    TextField::create('InternalItemID', _t('SilverShop\\Core\\Product.InternalItemID', 'Product Code/SKU'), '', 30),
                    DropdownField::create('ParentID', _t("SilverShop\\Core\\Product.Category", "Category"), $self->getCategoryOptions())
                    ->setDescription(_t("SilverShop\\Core\\Product.CategoryDescription", "This is the parent page or default category.")),
                    ListBoxField::create(
                        'ProductCategories',
                        _t("SilverShop\\Core\\Product.AdditionalCategories", "Additional Categories"),
                        $self->getCategoryOptionsNoParent()
                    )->setMultiple(true),
                    TextField::create('Model', _t('SilverShop\\Core\\Product.Model', 'Model'), '', 30),
                    CheckboxField::create('Featured', _t('SilverShop\\Core\\Product.Featured', 'Featured Product')),
                    CheckboxField::create('AllowPurchase', _t('SilverShop\\Core\\Product.AllowPurchase', 'Allow product to be purchased'), 1),
                    ]
                );

                $fields->addFieldsToTab(
                    'Root.Pricing', [
                    TextField::create('BasePrice', _t('SilverShop\\Core\\Product.db_BasePrice', 'Price'))
                    ->setDescription(_t('SilverShop\\Core\\Product.PriceDesc', "Base price to sell this product at."))
                    ->setMaxLength(12),
                    TextField::create('CostPrice', _t('SilverShop\\Core\\Product.db_CostPrice', 'Cost Price'))
                    ->setDescription(_t('SilverShop\\Core\\Product.CostPriceDescription', 'Wholesale price before markup.'))
                    ->setMaxLength(12),
                    ]
                );

                $fieldSubstitutes = [
                'LengthUnit' => $self::config()->length_unit
                ];

                $fields->addFieldsToTab(
                    'Root.Shipping', [
                    TextField::create(
                        'Weight',
                        _t(
                            'SilverShop\\Core\\Product.WeightWithUnit', 'Weight ({WeightUnit})', '', array(
                            'WeightUnit' => self::config()->weight_unit
                            )
                        ),
                        '',
                        12
                    ),
                    TextField::create(
                        'Height',
                        _t('SilverShop\\Core\\Product.HeightWithUnit', 'Height ({LengthUnit})', '', $fieldSubstitutes),
                        '',
                        12
                    ),
                    TextField::create(
                        'Width',
                        _t('SilverShop\\Core\\Product.WidthWithUnit', 'Width ({LengthUnit})', '', $fieldSubstitutes),
                        '',
                        12
                    ),
                    TextField::create(
                        'Depth',
                        _t('SilverShop\\Core\\Product.DepthWithUnit', 'Depth ({LengthUnit})', '', $fieldSubstitutes),
                        '',
                        12
                    ),
                    ]
                );

                if (!$fields->dataFieldByName('Image')) {
                    $fields->addFieldToTab(
                        'Root.Images',
                        UploadField::create('Image', _t('SilverShop\\Core\\Product.Image', 'Product Image'))
                    );
                }
            }
        );

        return parent::getCMSFields();
    }

    /**
     * Fix grid field heading displaying "page name"
     */
    public function fieldLabels($includerelations = true)
    {
        $labels = parent::fieldLabels($includerelations);
        $labels['Title'] = "Title";
        return $labels;
    }

    /**
     * Helper function for generating list of categories to select from.
     *
     * @return array categories
     */
    private function getCategoryOptions()
    {
        $categories = ProductCategory::get()->map('ID', 'NestedTitle')->toArray();
        $categories = array(
                0 => _t("SiteTree.PARENTTYPE_ROOT", "Top-level page"),
            ) + $categories;
        if ($this->ParentID && !($this->Parent() instanceof ProductCategory)) {
            $categories = array(
                    $this->ParentID => $this->Parent()->Title . " (" . $this->Parent()->i18n_singular_name() . ")",
                ) + $categories;
        }

        return $categories;
    }

    /**
     * Helper function for generating a list of additional categories excluding the main parent.
     *
     * @return array categories
     */
    private function getCategoryOptionsNoParent()
    {
        $ancestors = $this->getAncestors()->map('ID', 'ID');
        $categories = ProductCategory::get();
        if (!empty($ancestors)) {
            $categories->filter("ID:not", $ancestors);
        }
        return $categories->map('ID', 'NestedTitle')->toArray();
    }

    /**
     * Get ids of all categories that this product appears in.
     *
     * @return array ids list
     */
    public function getCategoryIDs()
    {
        $ids = array();
        //ancestors
        foreach ($this->getAncestors() as $ancestor) {
            $ids[$ancestor->ID] = $ancestor->ID;
        }
        //additional categories
        $ids += $this->ProductCategories()->getIDList();

        return $ids;
    }

    /**
     * Get all categories that this product appears in.
     *
     * @return DataList category data list
     */
    public function getCategories()
    {
        return ProductCategory::get()->byIDs($this->getCategoryIDs());
    }

    /**
     * Conditions for whether a product can be purchased:
     *  - global allow purchase is enabled
     *  - product AllowPurchase field is true
     *  - if variations, then one of them needs to be purchasable
     *  - if not variations, selling price must be above 0
     *
     * Other conditions may be added by decorating with the canPurchase function
     *
     * @param Member $member
     * @param int    $quantity
     *
     * @return boolean
     */
    public function canPurchase($member = null, $quantity = 1)
    {
        $global = self::config()->global_allow_purchase;
        if (!$global || !$this->AllowPurchase) {
            return false;
        }
        $allowpurchase = false;
        $extension = self::has_extension(ProductVariationsExtension::class);
        if ($extension && ProductVariation::get()->filter("ProductID", $this->ID)->first()) {
            foreach ($this->Variations() as $variation) {
                if ($variation->canPurchase($member, $quantity)) {
                    $allowpurchase = true;
                    break;
                }
            }
        } else {
            $allowpurchase = ($this->sellingPrice() > 0 || self::config()->allow_zero_price);
        }

        // Standard mechanism for accepting permission changes from decorators
        $permissions = $this->extend('canPurchase', $member, $quantity);
        $permissions[] = $allowpurchase;
        return min($permissions);
    }

    /**
     * Returns if the product is already in the shopping cart.
     *
     * @return boolean
     */
    public function IsInCart()
    {
        $item = $this->Item();
        return $item && $item->exists() && $item->Quantity > 0;
    }

    /**
     * Returns the order item which contains the product
     *
     * @return OrderItem
     */
    public function Item()
    {
        $filter = array();
        $this->extend('updateItemFilter', $filter);
        $item = ShoppingCart::singleton()->get($this, $filter);
        if (!$item) {
            //return dummy item so that we can still make use of Item
            $item = $this->createItem();
        }
        $this->extend('updateDummyItem', $item);
        return $item;
    }

    /**
     * @see Buyable::createItem()
     */
    public function createItem($quantity = 1, $filter = null)
    {
        $orderitem = self::config()->order_item;
        $item = new $orderitem();
        $item->ProductID = $this->ID;
        if ($filter) {
            //TODO: make this a bit safer, perhaps intersect with allowed fields
            $item->update($filter);
        }
        $item->Quantity = $quantity;
        return $item;
    }

    /**
     * The raw retail price the visitor will get when they
     * add to cart. Can include discounts or markups on the base price.
     */
    public function sellingPrice()
    {
        $price = $this->BasePrice;
        //TODO: this is not ideal, because prices manipulations will not happen in a known order
        $this->extend("updateSellingPrice", $price);
        //prevent negative values
        $price = $price < 0 ? 0 : $price;

        // NOTE: Ideally, this would be dependent on the locale but as of
        // now the Silverstripe Currency field type has 2 hardcoded all over
        // the place. In the mean time there is an issue where the displayed
        // unit price can not exactly equal the multiplied price on an order
        // (i.e. if the calculated price is 3.145 it will display as 3.15.
        // so if I put 10 of them in my cart I will expect the price to be
        // 31.50 not 31.45).
        return round($price, Order::config()->rounding_precision);
    }

    /**
     * This value is cased to Currency in temlates.
     */
    public function getPrice()
    {
        return $this->sellingPrice();
    }

    public function setPrice($price)
    {
        $price = $price < 0 ? 0 : $price;
        $this->setField("BasePrice", $price);
    }

    /**
     * Allow orphaned products to be viewed.
     */
    public function isOrphaned()
    {
        return false;
    }

    public function Link()
    {
        $link = parent::Link();
        $this->extend('updateLink', $link);
        return $link;
    }

    /**
     * If the product does not have an image, and a default image
     * is defined in SiteConfig, return that instead.
     *
     * @return Image
     */
    public function Image()
    {
        $image = $this->getComponent('Image');
        $this->extend('updateImage', $image);

        if ($image && $image->exists() && file_exists($image->getFullPath())) {
            return $image;
        }
        $image = SiteConfig::current_site_config()->DefaultProductImage();
        if ($image && $image->exists() && file_exists($image->getFullPath())) {
            return $image;
        }
        return $this->model->Image->newObject();
    }

    /**
     * Integration with opengraph module
     *
     * @see    https://github.com/tractorcow/silverstripe-opengraph
     * @return string opengraph type
     */
    public function getOGType()
    {
        return 'product';
    }

    /**
     * Integration with the opengraph module
     *
     * @return string url of product image
     */
    public function getOGImage()
    {
        if ($image = $this->Image()) {
            $min = self::config()->min_opengraph_img_size;
            $image = $min && $image->getWidth() < $min ? $image->setWidth($min) : $image;

            return Director::absoluteURL($image->URL);
        }
    }

    /**
     * Link to add this product to cart.
     *
     * @return string link
     */
    public function addLink()
    {
        return ShoppingCart_Controller::add_item_link($this);
    }

    /**
     * Link to remove one of this product from cart.
     *
     * @return string link
     */
    public function removeLink()
    {
        return ShoppingCart_Controller::remove_item_link($this);
    }

    /**
     * Link to remove all of this product from cart.
     *
     * @return string link
     */
    public function removeallLink()
    {
        return ShoppingCart_Controller::remove_all_item_link($this);
    }
}

class Product_Controller extends Page_Controller
{
    private static $allowed_actions = array(
        'Form',
        AddProductForm::class,
    );

    public         $formclass       = AddProductForm::class; //allow overriding the type of form used

    public function Form()
    {
        $formclass = $this->formclass;
        $form = new $formclass($this, "Form");
        $this->extend('updateForm', $form);
        return $form;
    }
}

class Product_OrderItem extends OrderItem
{
    private static $db      = array(
        'ProductVersion' => 'Int',
    );

    private static $has_one = array(
        'Product' => Product::class,
    );

    /**
     * the has_one join field to identify the buyable
     */
    private static $buyable_relationship = Product::class;

    /**
     * Get related product
     *  - live version if in cart, or
     *  - historical version if order is placed
     *
     * @param boolean $forcecurrent - force getting latest version of the product.
     *
     * @return Product
     */
    public function Product($forcecurrent = false)
    {
        //TODO: this might need some unit testing to make sure it compliles with comment description
        //ie use live if in cart (however I see no logic for checking cart status)
        if ($this->ProductID && $this->ProductVersion && !$forcecurrent) {
            return Versioned::get_version(Product::class, $this->ProductID, $this->ProductVersion);
        } elseif ($this->ProductID
            && $product = Versioned::get_one_by_stage(
                Product::class,
                "Live",
                "\"Product\".\"ID\"  = " . $this->ProductID
            )
        ) {
            return $product;
        }
        return false;
    }

    public function onPlacement()
    {
        parent::onPlacement();
        if ($product = $this->Product(true)) {
            $this->ProductVersion = $product->Version;
        }
    }

    public function TableTitle()
    {
        $product = $this->Product();
        $tabletitle = ($product) ? $product->Title : $this->i18n_singular_name();
        $this->extend('updateTableTitle', $tabletitle);
        return $tabletitle;
    }

    public function Link()
    {
        if ($product = $this->Product()) {
            return $product->Link();
        }
    }
}