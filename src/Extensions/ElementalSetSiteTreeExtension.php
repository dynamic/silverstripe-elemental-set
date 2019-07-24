<?php

namespace Dynamic\ElementalSets\Extensions;

use Dynamic\ElementalSets\Model\ElementalSet;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TreeMultiselectField;
use SilverStripe\ORM\DataExtension;

/**
 * Class ElementalSetSiteTreeExtension
 * @package Dynamic\ElementalSets\Extensions
 *
 * @property boolean InheritElementalSets
 *
 * @method \SilverStripe\ORM\ManyManyList DisabledSets
 */
class ElementalSetSiteTreeExtension extends DataExtension
{
    private static $db = [
        'InheritElementalSets' => 'Boolean',
    ];

    private static $many_many = [
        'DisabledSets' => ElementalSet::class,
    ];

    private static $defaults = [
        'InheritElementalSets' => 1,
    ];

    /**
     * @param FieldList $fields
     */
    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldsToTab(
            'Root.ElementSets',
            [
                CheckboxField::create('InheritElementalSets')
                    ->setTitle('Inherit Elements from Elemental Sets'),
                TreeMultiselectField::create('DisabledSets', 'Disabled Sets', ElementalSet::class),
            ]
        );
    }
}
