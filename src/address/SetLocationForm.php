<?php

namespace SilverShop\Core;

use Form;
use SiteConfig;
use FieldList;
use DropdownField;
use FormAction;
use Extension;
use SilverShop\Core\SetLocationForm;



class SetLocationForm extends Form
{
    public function __construct($controller, $name = SetLocationForm::class)
    {
        $countries = SiteConfig::current_site_config()->getCountriesList();
        $fields = FieldList::create(
            $countryfield = DropdownField::create("Country", _t('SilverShop\\Core\\SetLocationForm.Country', 'Country'), $countries)
        );
        $countryfield->setHasEmptyDefault(true);
        $countryfield->setEmptyString(_t('SilverShop\\Core\\SetLocationForm.ChooseCountry', 'Choose country...'));
        $actions = FieldList::create(
            FormAction::create("setLocation", "set")
        );
        parent::__construct($controller, $name, $fields, $actions);
        //load currently set location
        if ($location = ShopUserInfo::singleton()->getLocation()) {
            $countryfield->setHasEmptyDefault(false);
            $this->loadDataFrom($location);
        }
    }

    public function setLocation($data, $form)
    {
        ShopUserInfo::singleton()->setLocation($data);
        $this->controller->redirectBack();
    }
}

class LocationFormPageDecorator extends Extension
{
    public static $allowed_actions = array(
        SetLocationForm::class,
    );

    public function SetLocationForm()
    {
        return SetLocationForm::create($this->owner);
    }
}
