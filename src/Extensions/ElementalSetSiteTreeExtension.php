<?php

namespace Dynamic\ElementalSets\Extensions;

use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataExtension;

/**
 * Class ElementalSetSiteTreeExtension
 * @package Dynamic\ElementalSets\Extensions
 */
class ElementalSetSiteTreeExtension extends DataExtension
{
    private static $db = [
        'InheritElementalSets' => 'Boolean',
    ];

    private static $defaults = [
        'InheritElementalSets' => 1,
    ];

    /**
     * @param FieldList $fields
     */
    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldToTab(
            'Root.ElementSets',
            CheckboxField::create('InheritElementalSets')
                ->setTitle('Inherit Elements from Elemental Sets')
        );
    }
}